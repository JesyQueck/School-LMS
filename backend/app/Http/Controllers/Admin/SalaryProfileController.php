<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSalaryProfileComponentRequest;
use App\Http\Requests\StoreSalaryProfileRequest;
use App\Http\Requests\StoreSalaryRevisionRequest;
use App\Http\Requests\UpdateSalaryProfileRequest;
use App\Models\Employee;
use App\Models\SalaryComponent;
use App\Models\PayrollPeriod;
use App\Models\PayrollRecord;
use App\Models\SalaryProfile;
use App\Models\SalaryProfileComponent;
use App\Traits\AuditsActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalaryProfileController extends Controller
{
    use AuditsActions;

    public function index(Request $request)
    {
        $query = SalaryProfile::with([
            'employee.user',
            'components.salaryComponent',
        ])->orderByDesc('effective_from');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === 'true');
        }

        $profiles = $query->paginate(15)->withQueryString();

        return view('admin.payroll.profiles.index', [
            'profiles' => $profiles,
            'employees' => Employee::with('user')
                ->where('employment_status', Employee::STATUS_ACTIVE)
                ->orderBy('employee_number')
                ->get(),
            'filters' => $request->only(['employee_id', 'is_active']),
        ]);
    }

    public function create()
    {
        return view('admin.payroll.profiles.create', [
            'employees' => Employee::with('user')
                ->where('employment_status', Employee::STATUS_ACTIVE)
                ->orderBy('employee_number')
                ->get(),
            'components' => SalaryComponent::where('is_active', true)
            ->whereRaw('LOWER(name) != ?', ['basic salary'])
                ->orderBy('component_type')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(StoreSalaryProfileRequest $request)
    {
        $data = $request->validated();

        $componentsData = $data['components'] ?? [];
        unset($data['components']);

        $profile = DB::transaction(function () use ($data, $componentsData) {
            $profile = SalaryProfile::create($data);

            foreach ($componentsData as $comp) {
                if (empty($comp['enabled'])) {
                    continue;
                }

                $component = SalaryComponent::findOrFail($comp['component_id']);

                if (strcasecmp(trim($component->name), 'Basic Salary') === 0) {
                    continue;
                }

                $configuredValue = $component->name === 'Basic Salary'
                    ? $profile->basic_salary
                    : ($comp['value'] ?? $component->default_value);

                $profile->components()->create([
                    'salary_component_id' => $component->id,
                    'configured_value' => $configuredValue,
                    'effective_from' => $data['effective_from'],
                    'effective_to' => $data['effective_to'] ?? null,
                ]);
            }

            return $profile;
        });

        $auditData = $data;
        $auditData['components'] = $componentsData;

        $this->audit(
            $request,
            'salary_profile.created',
            SalaryProfile::class,
            $profile->id,
            null,
            $auditData
        );

        return redirect()->route('admin.payroll.profiles.show', $profile)
            ->with('status', 'Salary profile created.');
    }

    public function show(SalaryProfile $salaryProfile)
    {
        $salaryProfile->load([
            'employee.user',
            'components.salaryComponent',
        ]);

        return view('admin.payroll.profiles.show', [
            'profile' => $salaryProfile,
        ]);
    }

    public function edit(SalaryProfile $salaryProfile)
    {
        $salaryProfile->load([
            'employee.user',
            'components.salaryComponent',
        ]);

        return view('admin.payroll.profiles.edit', [
            'profile' => $salaryProfile,
            'employees' => Employee::with('user')
                ->orderBy('employee_number')
                ->get(),
            'components' => SalaryComponent::where('is_active', true)
            ->whereRaw('LOWER(name) != ?', ['basic salary'])
                ->orderBy('component_type')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(
        UpdateSalaryProfileRequest $request,
        SalaryProfile $salaryProfile
    ) {
        $data = $request->validated();

        $componentsData = $data['components'] ?? [];
        unset($data['components']);

        $old = [
            'employee_id' => $salaryProfile->employee_id,
            'basic_salary' => $salaryProfile->basic_salary,
            'effective_from' => $salaryProfile->effective_from,
            'effective_to' => $salaryProfile->effective_to,
            'is_active' => $salaryProfile->is_active,
            'components' => $salaryProfile->components()
                ->get()
                ->map(fn ($component) => [
                    'salary_component_id' => $component->salary_component_id,
                    'configured_value' => $component->configured_value,
                ])
                ->all(),
        ];

        DB::transaction(function () use (
            $salaryProfile,
            $data,
            $componentsData
        ) {
            $salaryProfile->update($data);

            $submittedComponentIds = collect($componentsData)
                ->pluck('component_id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            foreach ($componentsData as $comp) {
                $componentId = (int) $comp['component_id'];

                if (empty($comp['enabled'])) {
                    $salaryProfile->components()
                        ->where('salary_component_id', $componentId)
                        ->delete();

                    continue;
                }

                $component = SalaryComponent::findOrFail($componentId);

                if (strcasecmp(trim($component->name), 'Basic Salary') === 0) {
                    continue;
                }

                $configuredValue = $component->name === 'Basic Salary'
                    ? $salaryProfile->basic_salary
                    : ($comp['value'] ?? $component->default_value);

                $salaryProfile->components()->updateOrCreate(
                    [
                        'salary_component_id' => $component->id,
                    ],
                    [
                        'configured_value' => $configuredValue,
                        'effective_from' => $data['effective_from'],
                        'effective_to' => $data['effective_to'] ?? null,
                    ]
                );
            }
        });

        $auditData = $data;
        $auditData['components'] = $componentsData;

        $this->audit(
            $request,
            'salary_profile.updated',
            SalaryProfile::class,
            $salaryProfile->id,
            $old,
            $auditData
        );

        return redirect()->route('admin.payroll.profiles.show', $salaryProfile)
            ->with('status', 'Salary profile updated.');
    }

    public function addComponent(
        StoreSalaryProfileComponentRequest $request,
        SalaryProfile $salaryProfile
    ) {
        $data = $request->validated();

        $componentDefinition = SalaryComponent::findOrFail($data['salary_component_id']);

        if (strcasecmp(trim($componentDefinition->name), 'Basic Salary') === 0) {
            return back()->withErrors([
                'salary_component_id' => 'Basic Salary is managed through the salary profile basic salary field.',
            ]);
        }

        $component = $salaryProfile->components()->create($data);

        $this->audit(
            $request,
            'salary_profile_component.created',
            SalaryProfileComponent::class,
            $component->id,
            null,
            $data
        );

        return back()->with('status', 'Component added to salary profile.');
    }

    public function removeComponent(
        Request $request,
        SalaryProfile $salaryProfile,
        SalaryProfileComponent $profileComponent
    ) {
        $profileComponent->delete();

        $this->audit(
            $request,
            'salary_profile_component.deleted',
            SalaryProfileComponent::class,
            $profileComponent->id,
            null,
            null
        );

        return back()->with('status', 'Component removed from salary profile.');
    }


    public function revise(SalaryProfile $salaryProfile)
    {
        abort_unless(
            $salaryProfile->is_active && ! $salaryProfile->effective_to,
            404
        );

        $salaryProfile->load([
            'employee.user',
            'components.salaryComponent',
        ]);

        $eligiblePeriods = PayrollPeriod::query()
            ->where('status', PayrollPeriod::STATUS_DRAFT)
            ->whereDate('start_date', '>', $salaryProfile->effective_from->toDateString())
            ->whereDoesntHave('payrollRecords', function ($query) use ($salaryProfile) {
                $query->where('employee_id', $salaryProfile->employee_id);
            })
            ->orderBy('start_date')
            ->get();

        $oldDate = old('effective_from');

        $selectedPeriod = $eligiblePeriods->first(function ($period) use ($oldDate) {
            return $period->start_date->toDateString() === $oldDate;
        });

        $defaultEffectiveDate = (
            $selectedPeriod ?? $eligiblePeriods->first()
        )?->start_date?->toDateString();

        return view('admin.payroll.profiles.revise', [
            'profile' => $salaryProfile,
            'components' => SalaryComponent::where('is_active', true)
            ->whereRaw('LOWER(name) != ?', ['basic salary'])
                ->orderBy('component_type')
                ->orderBy('name')
                ->get(),
            'eligiblePeriods' => $eligiblePeriods,
            'defaultEffectiveDate' => $defaultEffectiveDate,
        ]);
    }
    public function storeRevision(
        StoreSalaryRevisionRequest $request,
        SalaryProfile $salaryProfile
    ) {
        $data = $request->validated();
        $date = $data['effective_from'];
        $previousDay = \Carbon\Carbon::parse($date)->subDay()->toDateString();

        if (! $salaryProfile->is_active || $salaryProfile->effective_to) {
            return back()->withErrors([
                'effective_from' => 'Only the current active salary profile can be revised.',
            ])->withInput();
        }

        if ($date <= $salaryProfile->effective_from->toDateString()) {
            return back()->withErrors([
                'effective_from' => 'The revision date must be after the current profile start date.',
            ])->withInput();
        }

        $period = PayrollPeriod::whereDate('start_date', $date)->first();

        if (! $period) {
            return back()->withErrors([
                'effective_from' => 'The revision date must be the first day of an existing payroll period.',
            ])->withInput();
        }

        if ($period->status !== PayrollPeriod::STATUS_DRAFT ||
            PayrollRecord::where('employee_id', $salaryProfile->employee_id)
                ->where('payroll_period_id', $period->id)
                ->exists()) {
            return back()->withErrors([
                'effective_from' => 'The target payroll period must be draft and have no generated payroll record for this employee.',
            ])->withInput();
        }

        $componentsData = $data['components'] ?? [];

        $result = DB::transaction(function () use (
            $salaryProfile,
            $data,
            $componentsData,
            $date,
            $previousDay
        ) {
            $profile = SalaryProfile::with('components.salaryComponent')
                ->lockForUpdate()
                ->findOrFail($salaryProfile->id);

            if (! $profile->is_active || $profile->effective_to) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'effective_from' => 'This salary profile has already been revised.',
                ]);
            }

            $oldSnapshot = [
                'basic_salary' => $profile->basic_salary,
                'effective_from' => $profile->effective_from->toDateString(),
                'effective_to' => $profile->effective_to?->toDateString(),
                'components' => $profile->components->map(fn ($item) => [
                    'component' => $item->salaryComponent?->name,
                    'value' => $item->configured_value,
                ])->all(),
            ];

            $profile->update(['effective_to' => $previousDay]);

            $profile->components()->update([
                'effective_to' => $previousDay,
            ]);

            $newProfile = SalaryProfile::create([
                'employee_id' => $profile->employee_id,
                'basic_salary' => $data['basic_salary'],
                'effective_from' => $date,
                'effective_to' => null,
                'is_active' => true,
            ]);

            foreach ($componentsData as $item) {
                if (empty($item['enabled'])) {
                    continue;
                }

                $component = SalaryComponent::where('is_active', true)
                    ->findOrFail($item['component_id']);

                if (strcasecmp(trim($component->name), 'Basic Salary') === 0) {
                    continue;
                }

                $value = $item['value'] ?? $component->default_value;

                $newProfile->components()->create([
                    'salary_component_id' => $component->id,
                    'configured_value' => $value,
                    'effective_from' => $date,
                    'effective_to' => null,
                ]);
            }

            return [
                'profile' => $newProfile,
                'old' => $oldSnapshot,
            ];
        });

        $newProfile = $result['profile'];

        $this->audit(
            $request,
            'salary_profile.revised',
            SalaryProfile::class,
            $newProfile->id,
            $result['old'],
            [
                'previous_profile_id' => $salaryProfile->id,
                'basic_salary' => $data['basic_salary'],
                'effective_from' => $date,
                'reason' => $data['reason'],
                'components' => $componentsData,
            ]
        );

        return redirect()
            ->route('admin.payroll.profiles.show', $newProfile)
            ->with('status', 'Salary revised successfully. Previous salary history was preserved.');
    }

    public function destroy(Request $request, SalaryProfile $salaryProfile)
    {
        $salaryProfile->delete();

        $this->audit(
            $request,
            'salary_profile.deleted',
            SalaryProfile::class,
            $salaryProfile->id,
            null,
            null
        );

        return redirect()->route('admin.payroll.profiles.index')
            ->with('status', 'Salary profile deleted.');
    }
}