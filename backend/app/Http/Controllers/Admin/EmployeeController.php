<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Models\User;
use App\Traits\AuditsActions;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    use AuditsActions;

    public function index(Request $request)
    {
        $query = Employee::with('user')->orderBy('employee_number');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('employee_number', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('employment_status', $request->status);
        }

        $employees = $query->paginate(15)->withQueryString();

        return view('admin.payroll.employees.index', [
            'employees' => $employees,
            'statuses' => Employee::STATUSES,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        $users = User::whereDoesntHave('employee')->orderBy('name')->get();

        return view('admin.payroll.employees.create', [
            'users' => $users,
            'employmentTypes' => Employee::EMPLOYMENT_TYPES,
            'statuses' => Employee::STATUSES,
        ]);
    }

    public function store(StoreEmployeeRequest $request)
    {
        $data = $request->validated();

        $employee = Employee::create($data);

        $this->audit($request, 'employee.created', Employee::class, $employee->id, null, $data);

        return redirect()->route('admin.payroll.employees.show', $employee)
            ->with('status', 'Employee '.$employee->employee_number.' created.');
    }

    public function show(Employee $employee)
    {
        $employee->load(['user', 'salaryProfiles.components.salaryComponent', 'payrollRecords.period']);

        return view('admin.payroll.employees.show', [
            'employee' => $employee,
            'statuses' => Employee::STATUSES,
        ]);
    }

    public function edit(Employee $employee)
    {
        $users = User::whereDoesntHave('employee')->orWhere('id', $employee->user_id)->orderBy('name')->get();

        return view('admin.payroll.employees.edit', [
            'employee' => $employee,
            'users' => $users,
            'employmentTypes' => Employee::EMPLOYMENT_TYPES,
            'statuses' => Employee::STATUSES,
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $old = $employee->getDirty();
        $data = $request->validated();

        $employee->update($data);

        $this->audit($request, 'employee.updated', Employee::class, $employee->id, $old, $data);

        return redirect()->route('admin.payroll.employees.show', $employee)
            ->with('status', 'Employee updated.');
    }

    public function deactivate(Request $request, Employee $employee)
    {
        $request->validate([
            'employment_status' => ['required', 'in:on_leave,terminated,retired'],
        ]);

        $old = $employee->getDirty();
        $employee->update(['employment_status' => $request->employment_status]);

        $this->audit($request, 'employee.deactivated', Employee::class, $employee->id, $old, [
            'employment_status' => $request->employment_status,
        ]);

        return back()->with('status', 'Employee '.$employee->employee_number.' marked as '.ucfirst(str_replace('_', ' ', $request->employment_status)).'.');
    }
}
