<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSalaryProfileComponentRequest;
use App\Http\Requests\StoreSalaryProfileRequest;
use App\Http\Requests\UpdateSalaryProfileRequest;
use App\Models\Employee;
use App\Models\SalaryComponent;
use App\Models\SalaryProfile;
use App\Traits\AuditsActions;
use Illuminate\Http\Request;

class SalaryProfileController extends Controller
{
    use AuditsActions;

    public function index(Request $request)
    {
        $query = SalaryProfile::with(['employee', 'components.salaryComponent'])->orderByDesc('effective_from');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === 'true');
        }

        $profiles = $query->paginate(15)->withQueryString();

        return view('admin.payroll.profiles.index', [
            'profiles' => $profiles,
            'employees' => Employee::where('employment_status', Employee::STATUS_ACTIVE)->orderBy('employee_number')->get(),
            'filters' => $request->only(['employee_id', 'is_active']),
        ]);
    }

    public function create()
    {
        return view('admin.payroll.profiles.create', [
            'employees' => Employee::where('employment_status', Employee::STATUS_ACTIVE)->orderBy('employee_number')->get(),
            'components' => SalaryComponent::where('is_active', true)->orderBy('component_type')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreSalaryProfileRequest $request)
    {
        $data = $request->validated();

        $profile = SalaryProfile::create($data);

        $this->audit($request, 'salary_profile.created', SalaryProfile::class, $profile->id, null, $data);

        return redirect()->route('admin.payroll.profiles.show', $profile)
            ->with('status', 'Salary profile created.');
    }

    public function show(SalaryProfile $salaryProfile)
    {
        $salaryProfile->load(['employee', 'components.salaryComponent']);

        return view('admin.payroll.profiles.show', [
            'profile' => $salaryProfile,
        ]);
    }

    public function edit(SalaryProfile $salaryProfile)
    {
        $salaryProfile->load('components.salaryComponent');

        return view('admin.payroll.profiles.edit', [
            'profile' => $salaryProfile,
            'employees' => Employee::orderBy('employee_number')->get(),
            'components' => SalaryComponent::where('is_active', true)->orderBy('component_type')->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateSalaryProfileRequest $request, SalaryProfile $salaryProfile)
    {
        $old = $salaryProfile->getDirty();
        $data = $request->validated();

        $salaryProfile->update($data);

        $this->audit($request, 'salary_profile.updated', SalaryProfile::class, $salaryProfile->id, $old, $data);

        return redirect()->route('admin.payroll.profiles.show', $salaryProfile)
            ->with('status', 'Salary profile updated.');
    }

    public function addComponent(StoreSalaryProfileComponentRequest $request, SalaryProfile $salaryProfile)
    {
        $data = $request->validated();

        $component = $salaryProfile->components()->create($data);

        $this->audit($request, 'salary_profile_component.created', \App\Models\SalaryProfileComponent::class, $component->id, null, $data);

        return back()->with('status', 'Component added to salary profile.');
    }

    public function removeComponent(Request $request, SalaryProfile $salaryProfile, \App\Models\SalaryProfileComponent $profileComponent)
    {
        $profileComponent->delete();

        $this->audit($request, 'salary_profile_component.deleted', \App\Models\SalaryProfileComponent::class, $profileComponent->id, null, null);

        return back()->with('status', 'Component removed from salary profile.');
    }

    public function destroy(Request $request, SalaryProfile $salaryProfile)
    {
        $salaryProfile->delete();

        $this->audit($request, 'salary_profile.deleted', SalaryProfile::class, $salaryProfile->id, null, null);

        return redirect()->route('admin.payroll.profiles.index')
            ->with('status', 'Salary profile deleted.');
    }
}
