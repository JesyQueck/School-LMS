<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSalaryComponentRequest;
use App\Http\Requests\UpdateSalaryComponentRequest;
use App\Models\SalaryComponent;
use App\Traits\AuditsActions;
use Illuminate\Http\Request;

class SalaryComponentController extends Controller
{
    use AuditsActions;

    public function index(Request $request)
    {
        $query = SalaryComponent::orderBy('component_type')->orderBy('name');

        if ($request->filled('type')) {
            $query->where('component_type', $request->type);
        }

        $components = $query->paginate(15)->withQueryString();

        return view('admin.payroll.components.index', [
            'components' => $components,
            'types' => [
                SalaryComponent::TYPE_ALLOWANCE => 'Allowance',
                SalaryComponent::TYPE_DEDUCTION => 'Deduction',
            ],
            'filters' => $request->only(['type']),
        ]);
    }

    public function create()
    {
        return view('admin.payroll.components.create', [
            'types' => [
                SalaryComponent::TYPE_ALLOWANCE => 'Allowance',
                SalaryComponent::TYPE_DEDUCTION => 'Deduction',
            ],
            'calculationTypes' => [
                SalaryComponent::CALC_FIXED => 'Fixed',
                SalaryComponent::CALC_PERCENTAGE => 'Percentage',
            ],
        ]);
    }

    public function store(StoreSalaryComponentRequest $request)
    {
        $data = $request->validated();

        $component = SalaryComponent::create($data);

        $this->audit($request, 'salary_component.created', SalaryComponent::class, $component->id, null, $data);

        return redirect()->route('admin.payroll.components.show', $component)
            ->with('status', 'Salary component created.');
    }

    public function show(SalaryComponent $salaryComponent)
    {
        $salaryComponent->load('profileAssignments.salaryProfile.employee');

        return view('admin.payroll.components.show', [
            'salaryComponent' => $salaryComponent,
        ]);
    }

    public function edit(SalaryComponent $salaryComponent)
    {
        return view('admin.payroll.components.edit', [
            'salaryComponent' => $salaryComponent,
            'types' => [
                SalaryComponent::TYPE_ALLOWANCE => 'Allowance',
                SalaryComponent::TYPE_DEDUCTION => 'Deduction',
            ],
            'calculationTypes' => [
                SalaryComponent::CALC_FIXED => 'Fixed',
                SalaryComponent::CALC_PERCENTAGE => 'Percentage',
            ],
        ]);
    }

    public function update(UpdateSalaryComponentRequest $request, SalaryComponent $salaryComponent)
    {
        $old = $salaryComponent->getDirty();
        $data = $request->validated();

        $salaryComponent->update($data);

        $this->audit($request, 'salary_component.updated', SalaryComponent::class, $salaryComponent->id, $old, $data);

        return redirect()->route('admin.payroll.components.show', $salaryComponent)
            ->with('status', 'Salary component updated.');
    }

    public function destroy(Request $request, SalaryComponent $salaryComponent)
    {
        $name = $salaryComponent->name;
        $salaryComponent->delete();

        $this->audit($request, 'salary_component.deleted', SalaryComponent::class, $salaryComponent->id, ['name' => $name], null);

        return redirect()->route('admin.payroll.components.index')
            ->with('status', 'Salary component deleted.');
    }
}
