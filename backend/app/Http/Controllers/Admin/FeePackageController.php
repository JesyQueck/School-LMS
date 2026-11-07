<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeePackageRequest;
use App\Http\Requests\UpdateFeePackageRequest;
use App\Models\AcademicSession;
use App\Models\FeeComponent;
use App\Models\FeePackage;
use App\Models\FeePackageAssignment;
use App\Models\SchoolClass;
use App\Models\Term;
use App\Services\FeeService;
use App\Traits\AuditsActions;
use Illuminate\Http\Request;

class FeePackageController extends Controller
{
    use AuditsActions;

    public function __construct(
        protected FeeService $feeService,
    ) {}

    public function index(Request $request)
    {
        $packages = FeePackage::with('components')->latest()->paginate(15);

        return view('admin.finance.packages.index', [
            'packages' => $packages,
        ]);
    }

    public function create()
    {
        return view('admin.finance.packages.create', [
            'terms' => Term::all(),
            'classes' => \App\Models\SchoolClass::all(),
        ]);
    }

    public function store(StoreFeePackageRequest $request)
    {
        $data = $request->validated();
        $components = $data['components'] ?? [];

        $package = DB::transaction(function () use ($data, $components) {
            $package = FeePackage::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'created_by' => auth()->id(),
            ]);

            foreach ($components as $index => $component) {
                FeeComponent::create([
                    'fee_package_id' => $package->id,
                    'name' => $component['name'],
                    'description' => $component['description'] ?? null,
                    'amount' => $component['amount'],
                    'sort_order' => $index,
                ]);
            }

            return $package->load('components');
        });

        $this->audit($request, 'fee_package.created', FeePackage::class, $package->id, null, $data);

        return redirect()->route('admin.finance.packages.index')
            ->with('status', 'Fee package created with '.count($components).' component(s). Total: ₦'.number_format($package->total, 2));
    }

    public function show(FeePackage $feePackage)
    {
        $feePackage->load(['components', 'assignments.class', 'assignments.term', 'assignments.academicSession']);

        return view('admin.finance.packages.show', [
            'package' => $feePackage,
        ]);
    }

    public function edit(FeePackage $feePackage)
    {
        $feePackage->load('components');

        return view('admin.finance.packages.edit', [
            'package' => $feePackage,
        ]);
    }

    public function update(UpdateFeePackageRequest $request, FeePackage $feePackage)
    {
        $data = $request->validated();
        $components = $data['components'] ?? [];

        DB::transaction(function () use ($feePackage, $data, $components) {
            $feePackage->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            // Delete existing components and create new ones
            // Note: This only works if the package has no assignments yet
            // For assigned packages, we should prevent component changes
            if ($feePackage->assignments()->exists()) {
                throw new \InvalidArgumentException('Cannot modify components of an assigned package. Create a new package instead.');
            }

            $feePackage->components()->delete();

            foreach ($components as $index => $component) {
                FeeComponent::create([
                    'fee_package_id' => $feePackage->id,
                    'name' => $component['name'],
                    'description' => $component['description'] ?? null,
                    'amount' => $component['amount'],
                    'sort_order' => $index,
                ]);
            }
        });

        $this->audit($request, 'fee_package.updated', FeePackage::class, $feePackage->id, null, $data);

        return redirect()->route('admin.finance.packages.show', $feePackage)
            ->with('status', 'Fee package updated.');
    }

    public function destroy(FeePackage $feePackage)
    {
        if ($feePackage->assignments()->exists()) {
            return back()->with('error', 'Cannot delete a package that has assignments. Deactivate it instead.');
        }

        $feePackage->components()->delete();
        $feePackage->delete();

        $this->audit(request(), 'fee_package.deleted', FeePackage::class, $feePackage->id, ['name' => $feePackage->name], null);

        return redirect()->route('admin.finance.packages.index')
            ->with('status', 'Fee package deleted.');
    }

    public function assignments(Request $request)
    {
        $assignments = FeePackageAssignment::with(['package.components', 'class', 'term', 'academicSession', 'assignedBy'])
            ->when($request->filled('class_id'), fn ($q) => $q->where('class_id', $request->class_id))
            ->when($request->filled('term_id'), fn ($q) => $q->where('term_id', $request->term_id))
            ->when($request->filled('package_id'), fn ($q) => $q->where('fee_package_id', $request->package_id))
            ->latest()
            ->paginate(15)
            ->appends($request->only(['class_id', 'term_id', 'package_id']));

        return view('admin.finance.packages.assignments', [
            'assignments' => $assignments,
            'packages' => \App\Models\FeePackage::where('is_active', true)->get(),
            'classes' => \App\Models\SchoolClass::all(),
            'terms' => Term::all(),
            'academicSessions' => \App\Models\AcademicSession::all(),
            'filters' => $request->only(['class_id', 'term_id', 'package_id']),
        ]);
    }

   public function assign(Request $request)
{
    $data = $request->validate([
        'fee_package_id' => ['required', 'exists:fee_packages,id'],
        'class_id' => ['required', 'exists:classes,id'],
        'term_id' => ['required', 'exists:terms,id'],
        'academic_session_id' => ['required', 'exists:academic_sessions,id'],
    ]);

    // Ensure the selected term belongs to the selected academic session.
    $term = Term::findOrFail($data['term_id']);

    if ((int) $term->academic_session_id !== (int) $data['academic_session_id']) {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'term_id' => 'The selected term does not belong to the selected academic session.',
        ]);
    }

    $assignment = FeePackageAssignment::firstOrCreate(
        [
            'fee_package_id' => $data['fee_package_id'],
            'class_id' => $data['class_id'],
            'term_id' => $data['term_id'],
        ],
        [
            'academic_session_id' => $data['academic_session_id'],
            'is_active' => true,
            'assigned_by' => auth()->id(),
        ]
    );

    if ($assignment->wasRecentlyCreated) {
        $this->feeService->createStudentFeesForPackageAssignment($assignment);
        $message = 'Package assigned and student fees generated.';
    } else {
        $assignment->update([
            'is_active' => true,
            'assigned_by' => auth()->id(),
        ]);

        $this->feeService->createStudentFeesForPackageAssignment($assignment);
        $message = 'Package assignment reactivated and student fees synchronized.';
    }

    $this->audit(
        $request,
        'fee_package.assigned',
        FeePackageAssignment::class,
        $assignment->id,
        null,
        $data
    );

    return redirect()
        ->route('admin.finance.packages.assignments')
        ->with('status', $message);
}

    public function deactivateAssignment(FeePackageAssignment $assignment)
    {
        $assignment->update(['is_active' => false]);

        $this->audit(request(), 'fee_package.assignment_deactivated', FeePackageAssignment::class, $assignment->id, null, null);

        return back()->with('status', 'Package assignment deactivated.');
    }
}