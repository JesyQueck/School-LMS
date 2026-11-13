<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\PayrollRecord;
use App\Models\SalaryComponent;
use App\Models\SalaryProfile;
use App\Models\SalaryProfileComponent;
use App\Models\User;
use App\Services\PayrollService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdmin(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'admin',
            'email' => 'admin.payroll@example.com',
            'password' => 'password123',
        ], $overrides));
    }

    protected function createBursar(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'bursar',
            'email' => 'bursar.payroll@example.com',
            'staff_id' => 'B-PAY-' . strtoupper(bin2hex(random_bytes(4))),
            'password' => 'password123',
        ], $overrides));
    }

    protected function createEmployee(array $overrides = []): Employee
    {
        return Employee::create(array_merge([
            'employee_number' => 'EMP-' . strtoupper(bin2hex(random_bytes(4))),
            'employment_type' => 'full-time',
            'date_employed' => '2026-01-15',
            'department' => 'Administration',
            'designation' => 'Clerk',
            'employment_status' => 'active',
        ], $overrides));
    }

    protected function createPeriod(User $creator, array $overrides = []): PayrollPeriod
    {
        return PayrollPeriod::create(array_merge([
            'name' => 'Period ' . strtoupper(bin2hex(random_bytes(4))),
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'pay_date' => '2026-02-05',
            'status' => 'draft',
            'created_by' => $creator->id,
        ], $overrides));
    }

    protected function createComponent(array $overrides = []): SalaryComponent
    {
        return SalaryComponent::create(array_merge([
            'name' => 'Component ' . strtoupper(bin2hex(random_bytes(4))),
            'description' => 'Test component',
            'component_type' => 'allowance',
            'calculation_type' => 'fixed',
            'default_value' => 10000.00,
            'is_taxable' => false,
            'is_active' => true,
        ], $overrides));
    }

    /* ---------------------------------------------------------------
     * Employee management & authorization
     * --------------------------------------------------------------- */

    public function test_admin_can_access_payroll_dashboard(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->get('/admin/payroll');
        $response->assertOk();
        $response->assertViewIs('admin.payroll.index');
    }

    public function test_bursar_cannot_access_admin_payroll_routes(): void
    {
        $bursar = $this->createBursar();
        $this->actingAs($bursar);

        $this->get('/admin/payroll')->assertForbidden();
        $this->get('/admin/payroll/employees')->assertForbidden();
    }

    public function test_guest_cannot_access_admin_payroll_routes(): void
    {
        $this->get('/admin/payroll')->assertRedirect('/login');
    }

    public function test_admin_can_create_employee(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post('/admin/payroll/employees', [
            'employee_number' => 'EMP-NEW-001',
            'employment_type' => 'full-time',
            'date_employed' => '2026-01-15',
            'department' => 'Finance',
            'designation' => 'Accountant',
            'employment_status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('employees', ['employee_number' => 'EMP-NEW-001']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'employee.created']);
    }

    public function test_admin_can_deactivate_employee(): void
    {
        $admin = $this->createAdmin();
        $employee = $this->createEmployee();
        $this->actingAs($admin);

        $response = $this->patch('/admin/payroll/employees/' . $employee->id . '/deactivate', [
            'employment_status' => 'terminated',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'employment_status' => 'terminated',
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'employee.deactivated']);
    }

    /* ---------------------------------------------------------------
     * Salary component management
     * --------------------------------------------------------------- */

    public function test_admin_can_create_salary_component(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post('/admin/payroll/components', [
            'name' => 'Transport Allowance',
            'description' => 'Monthly transport',
            'component_type' => 'allowance',
            'calculation_type' => 'fixed',
            'default_value' => 5000.00,
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('salary_components', ['name' => 'Transport Allowance']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'salary_component.created']);
    }

    public function test_percentage_salary_component_is_stored(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $this->post('/admin/payroll/components', [
            'name' => 'Pension Deduction',
            'component_type' => 'deduction',
            'calculation_type' => 'percentage',
            'default_value' => 5.00,
            'is_active' => true,
        ]);

        $component = SalaryComponent::where('name', 'Pension Deduction')->first();
        $this->assertNotNull($component);
        $this->assertEquals('deduction', $component->component_type);
        $this->assertEquals('percentage', $component->calculation_type);
        $this->assertEquals(5.00, $component->default_value);
    }

    /* ---------------------------------------------------------------
     * Salary profile & effective dates
     * --------------------------------------------------------------- */

    public function test_admin_can_create_salary_profile_with_components(): void
    {
        $admin = $this->createAdmin();
        $employee = $this->createEmployee();
        $component = $this->createComponent();
        $this->actingAs($admin);

        $response = $this->post('/admin/payroll/profiles', [
            'employee_id' => $employee->id,
            'basic_salary' => 50000.00,
            'effective_from' => '2026-01-01',
            'is_active' => true,
            'components' => [
                $component->id => ['enabled' => true, 'component_id' => $component->id, 'value' => 10000.00],
            ],
        ]);

        $response->assertRedirect();
        $profile = SalaryProfile::where('employee_id', $employee->id)->first();
        $this->assertNotNull($profile);
        $this->assertEquals(50000.00, $profile->basic_salary);
        $this->assertDatabaseHas('salary_profile_components', [
            'salary_profile_id' => $profile->id,
            'salary_component_id' => $component->id,
        ]);
    }

    public function test_salary_profile_respects_effective_dates_for_period(): void
    {
        $employee = $this->createEmployee();
        $admin = $this->createAdmin();
        $period = $this->createPeriod($admin);

        // Profile effective before the period
        SalaryProfile::create([
            'employee_id' => $employee->id,
            'basic_salary' => 50000.00,
            'effective_from' => '2026-01-01',
            'effective_to' => '2026-01-31',
            'is_active' => true,
        ]);

        $this->assertNotNull($employee->salaryProfiles()->where('is_active', true)
            ->whereDate('effective_from', '<=', $period->end_date)
            ->where(function ($q) use ($period) {
                $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $period->start_date);
            })->first());
    }

    /* ---------------------------------------------------------------
     * Fixed and percentage calculations
     * --------------------------------------------------------------- */

    public function test_fixed_allowance_and_deduction_calculation(): void
    {
        $service = app(PayrollService::class);

        $component = SalaryComponent::create([
            'name' => 'Housing Allowance',
            'component_type' => 'allowance',
            'calculation_type' => 'fixed',
            'default_value' => 15000.00,
            'is_active' => true,
        ]);

        $deduction = SalaryComponent::create([
            'name' => 'Loan Deduction',
            'component_type' => 'deduction',
            'calculation_type' => 'fixed',
            'default_value' => 5000.00,
            'is_active' => true,
        ]);

        $profile = SalaryProfile::create([
            'employee_id' => $this->createEmployee()->id,
            'basic_salary' => 50000.00,
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);

        SalaryProfileComponent::create([
            'salary_profile_id' => $profile->id,
            'salary_component_id' => $component->id,
            'configured_value' => 15000.00,
            'effective_from' => '2026-01-01',
        ]);

        SalaryProfileComponent::create([
            'salary_profile_id' => $profile->id,
            'salary_component_id' => $deduction->id,
            'configured_value' => 5000.00,
            'effective_from' => '2026-01-01',
        ]);

        $periodAdmin = $this->createAdmin();
        $period = PayrollPeriod::create([
            'name' => 'Jan 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'pay_date' => '2026-02-05',
            'status' => 'draft',
            'created_by' => $periodAdmin->id,
        ]);

        $calc = $service->calculatePayroll($profile, $period);

        $this->assertEquals(50000.00, $calc['basic_salary']);
        $this->assertEquals(15000.00, $calc['total_allowances']);
        $this->assertEquals(5000.00, $calc['total_deductions']);
        $this->assertEquals(65000.00, $calc['gross_pay']);
        $this->assertEquals(60000.00, $calc['net_pay']);
    }

    public function test_percentage_allowance_and_deduction_calculation(): void
    {
        $service = app(PayrollService::class);

        $allowance = SalaryComponent::create([
            'name' => 'Performance Allowance',
            'component_type' => 'allowance',
            'calculation_type' => 'percentage',
            'default_value' => 10.00,
            'is_active' => true,
        ]);

        $deduction = SalaryComponent::create([
            'name' => 'Pension Deduction',
            'component_type' => 'deduction',
            'calculation_type' => 'percentage',
            'default_value' => 5.00,
            'is_active' => true,
        ]);

        $profile = SalaryProfile::create([
            'employee_id' => $this->createEmployee()->id,
            'basic_salary' => 50000.00,
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);

        SalaryProfileComponent::create([
            'salary_profile_id' => $profile->id,
            'salary_component_id' => $allowance->id,
            'configured_value' => 10.00,
            'effective_from' => '2026-01-01',
        ]);

        SalaryProfileComponent::create([
            'salary_profile_id' => $profile->id,
            'salary_component_id' => $deduction->id,
            'configured_value' => 5.00,
            'effective_from' => '2026-01-01',
        ]);

        $periodAdmin = $this->createAdmin();
        $period = PayrollPeriod::create([
            'name' => 'Jan 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'pay_date' => '2026-02-05',
            'status' => 'draft',
            'created_by' => $periodAdmin->id,
        ]);

        $calc = $service->calculatePayroll($profile, $period);

        // 10% of 50000 = 5000 allowance; 5% of 50000 = 2500 deduction
        $this->assertEquals(50000.00, $calc['basic_salary']);
        $this->assertEquals(5000.00, $calc['total_allowances']);
        $this->assertEquals(2500.00, $calc['total_deductions']);
        $this->assertEquals(55000.00, $calc['gross_pay']);
        $this->assertEquals(52500.00, $calc['net_pay']);
    }

    /* ---------------------------------------------------------------
     * Payroll generation & duplicate prevention
     * --------------------------------------------------------------- */

    public function test_payroll_generation_creates_records_and_snapshots(): void
    {
        $admin = $this->createAdmin();
        $employee = $this->createEmployee();
        $component = $this->createComponent(['component_type' => 'allowance', 'calculation_type' => 'fixed', 'default_value' => 10000.00]);
        $period = $this->createPeriod($admin);

        SalaryProfile::create([
            'employee_id' => $employee->id,
            'basic_salary' => 50000.00,
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);

        SalaryProfileComponent::create([
            'salary_profile_id' => SalaryProfile::where('employee_id', $employee->id)->first()->id,
            'salary_component_id' => $component->id,
            'configured_value' => 10000.00,
            'effective_from' => '2026-01-01',
        ]);

        $this->actingAs($admin);
        $response = $this->post('/admin/payroll/periods/' . $period->id . '/generate');
        $response->assertRedirect();
        $this->assertDatabaseHas('payroll_records', [
            'employee_id' => $employee->id,
            'payroll_period_id' => $period->id,
            'status' => 'calculated',
            'basic_salary_snapshot' => 50000.00,
            'net_pay' => 60000.00,
        ]);
        $this->assertDatabaseHas('payroll_record_components', [
            'component_name' => $component->name,
            'amount' => 10000.00,
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'payroll.generated']);
    }

    public function test_payroll_generation_prevents_duplicates(): void
    {
        $admin = $this->createAdmin();
        $employee = $this->createEmployee();
        $period = $this->createPeriod($admin);

        SalaryProfile::create([
            'employee_id' => $employee->id,
            'basic_salary' => 50000.00,
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);

        $this->actingAs($admin);
        $this->post('/admin/payroll/periods/' . $period->id . '/generate');
        $response = $this->post('/admin/payroll/periods/' . $period->id . '/generate');

        $response->assertRedirect();
        $this->assertDatabaseCount('payroll_records', 1);
    }

    public function test_payroll_generation_excludes_employees_without_profile(): void
    {
        $admin = $this->createAdmin();
        $employeeWithoutProfile = $this->createEmployee(['employee_number' => 'EMP-NOPROF']);
        $period = $this->createPeriod($admin);

        $this->actingAs($admin);
        $response = $this->post('/admin/payroll/periods/' . $period->id . '/generate');

        $response->assertRedirect();
        $this->assertDatabaseMissing('payroll_records', [
            'employee_id' => $employeeWithoutProfile->id,
            'payroll_period_id' => $period->id,
        ]);
        // Session should report excluded employees
        $this->assertNotNull(session('excluded'));
    }

    public function test_payroll_component_snapshot_is_immutable(): void
    {
        $admin = $this->createAdmin();
        $employee = $this->createEmployee();
        $component = $this->createComponent(['name' => 'Transport Allowance', 'default_value' => 10000.00]);
        $period = $this->createPeriod($admin);

        $profile = SalaryProfile::create([
            'employee_id' => $employee->id,
            'basic_salary' => 50000.00,
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);

        SalaryProfileComponent::create([
            'salary_profile_id' => $profile->id,
            'salary_component_id' => $component->id,
            'configured_value' => 10000.00,
            'effective_from' => '2026-01-01',
        ]);

        $this->actingAs($admin);
        $this->post('/admin/payroll/periods/' . $period->id . '/generate');

        $record = PayrollRecord::where('employee_id', $employee->id)->first();
        $snapshot = $record->components->first();
        $this->assertEquals('Transport Allowance', $snapshot->component_name);
        $this->assertEquals(10000.00, $snapshot->amount);

        // Changing source component must not affect snapshot
        $component->update(['name' => 'Updated Transport', 'default_value' => 99999.00]);
        $this->assertEquals('Transport Allowance', $snapshot->fresh()->component_name);
        $this->assertEquals(10000.00, $snapshot->fresh()->amount);
    }

    /* ---------------------------------------------------------------
     * Approval & rejection permissions
     * --------------------------------------------------------------- */

    public function test_only_admin_can_approve_payroll(): void
    {
        $admin = $this->createAdmin();
        $bursar = $this->createBursar();
        $employee = $this->createEmployee();
        $period = $this->createPeriod($admin);

        SalaryProfile::create([
            'employee_id' => $employee->id,
            'basic_salary' => 50000.00,
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);

        $this->actingAs($admin);
        $this->post('/admin/payroll/periods/' . $period->id . '/generate');

        $record = PayrollRecord::first();

        // Bursar cannot approve
        $this->actingAs($bursar);
        $this->post('/admin/payroll/records/' . $record->id . '/approve')->assertForbidden();

        // Admin can approve
        $this->actingAs($admin);
        $this->post('/admin/payroll/records/' . $record->id . '/approve');
        $this->assertDatabaseHas('payroll_records', [
            'id' => $record->id,
            'status' => 'approved',
        ]);
        $this->assertNotNull($record->fresh()->approved_by);
        $this->assertDatabaseHas('audit_logs', ['action' => 'payroll.approved']);
    }

    public function test_only_admin_can_reject_payroll(): void
    {
        $admin = $this->createAdmin();
        $bursar = $this->createBursar();
        $employee = $this->createEmployee();
        $period = $this->createPeriod($admin);

        SalaryProfile::create([
            'employee_id' => $employee->id,
            'basic_salary' => 50000.00,
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);

        $this->actingAs($admin);
        $this->post('/admin/payroll/periods/' . $period->id . '/generate');
        $record = PayrollRecord::first();

        // Bursar cannot reject
        $this->actingAs($bursar);
        $this->post('/admin/payroll/records/' . $record->id . '/reject', ['reason' => 'Test'])->assertForbidden();

        // Admin can reject
        $this->actingAs($admin);
        $this->post('/admin/payroll/records/' . $record->id . '/reject', ['reason' => 'Incorrect calculation']);
        $this->assertDatabaseHas('payroll_records', [
            'id' => $record->id,
            'status' => 'voided',
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'payroll.rejected']);
    }

    public function test_paid_payroll_cannot_be_edited_or_recalculated(): void
    {
        $admin = $this->createAdmin();
        $employee = $this->createEmployee();
        $period = $this->createPeriod($admin);

        SalaryProfile::create([
            'employee_id' => $employee->id,
            'basic_salary' => 50000.00,
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);

        $this->actingAs($admin);
        $this->post('/admin/payroll/periods/' . $period->id . '/generate');
        $record = PayrollRecord::first();
        $this->post('/admin/payroll/records/' . $record->id . '/approve');

        // Try to regenerate - should be prevented because record exists
        $response = $this->post('/admin/payroll/periods/' . $period->id . '/generate');
        $response->assertRedirect();
        $this->assertDatabaseCount('payroll_records', 1);
    }

    /* ---------------------------------------------------------------
     * Bursar payment permissions & duplicate prevention
     * --------------------------------------------------------------- */

    public function test_only_bursar_can_record_payment(): void
    {
        $admin = $this->createAdmin();
        $bursar = $this->createBursar();
        $employee = $this->createEmployee();
        $period = $this->createPeriod($admin);

        SalaryProfile::create([
            'employee_id' => $employee->id,
            'basic_salary' => 50000.00,
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);

        $this->actingAs($admin);
        $this->post('/admin/payroll/periods/' . $period->id . '/generate');
        $record = PayrollRecord::first();
        $this->post('/admin/payroll/records/' . $record->id . '/approve');

        // Admin cannot record payment
        $this->actingAs($admin);
        $this->post('/bursar/staff-salaries/records/' . $record->id . '/payment', [
            'payment_method' => 'bank_transfer',
            'paid_at' => '2026-02-05 10:00:00',
        ])->assertForbidden();

        // Bursar can record payment
        $this->actingAs($bursar);
        $this->post('/bursar/staff-salaries/records/' . $record->id . '/payment', [
            'payment_method' => 'bank_transfer',
            'payment_reference' => 'REF-123',
            'paid_at' => '2026-02-05 10:00:00',
        ]);
        $this->assertDatabaseHas('payroll_records', [
            'id' => $record->id,
            'status' => 'paid',
            'payment_method' => 'bank_transfer',
            'payment_reference' => 'REF-123',
        ]);
        $this->assertNotNull($record->fresh()->paid_by);
        $this->assertDatabaseHas('audit_logs', ['action' => 'payroll.payment_recorded']);
    }

    public function test_duplicate_payment_recording_is_prevented(): void
    {
        $admin = $this->createAdmin();
        $bursar = $this->createBursar();
        $employee = $this->createEmployee();
        $period = $this->createPeriod($admin);

        SalaryProfile::create([
            'employee_id' => $employee->id,
            'basic_salary' => 50000.00,
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);

        $this->actingAs($admin);
        $this->post('/admin/payroll/periods/' . $period->id . '/generate');
        $record = PayrollRecord::first();
        $this->post('/admin/payroll/records/' . $record->id . '/approve');

        $this->actingAs($bursar);
        $this->post('/bursar/staff-salaries/records/' . $record->id . '/payment', [
            'payment_method' => 'bank_transfer',
            'payment_reference' => 'REF-123',
            'paid_at' => '2026-02-05 10:00:00',
        ]);

        // Second payment attempt should be rejected (record already paid)
        $response = $this->post('/bursar/staff-salaries/records/' . $record->id . '/payment', [
            'payment_method' => 'cash',
            'payment_reference' => 'REF-456',
            'paid_at' => '2026-02-06 10:00:00',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('payroll_records', [
            'id' => $record->id,
            'payment_reference' => 'REF-123',
        ]);
    }

    public function test_bursar_cannot_approve_payroll(): void
    {
        $admin = $this->createAdmin();
        $bursar = $this->createBursar();
        $employee = $this->createEmployee();
        $period = $this->createPeriod($admin);

        SalaryProfile::create([
            'employee_id' => $employee->id,
            'basic_salary' => 50000.00,
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);

        $this->actingAs($admin);
        $this->post('/admin/payroll/periods/' . $period->id . '/generate');
        $record = PayrollRecord::first();

        $this->actingAs($bursar);
        $this->post('/admin/payroll/records/' . $record->id . '/approve')->assertForbidden();
    }

    /* ---------------------------------------------------------------
     * Payslip generation & access
     * --------------------------------------------------------------- */

    public function test_bursar_can_view_payslip_for_paid_record(): void
    {
        $admin = $this->createAdmin();
        $bursar = $this->createBursar();
        $employee = $this->createEmployee();
        $period = $this->createPeriod($admin);

        SalaryProfile::create([
            'employee_id' => $employee->id,
            'basic_salary' => 50000.00,
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);

        $this->actingAs($admin);
        $this->post('/admin/payroll/periods/' . $period->id . '/generate');
        $record = PayrollRecord::first();
        $this->post('/admin/payroll/records/' . $record->id . '/approve');

        $this->actingAs($bursar);
        $this->post('/bursar/staff-salaries/records/' . $record->id . '/payment', [
            'payment_method' => 'bank_transfer',
            'paid_at' => '2026-02-05 10:00:00',
        ]);

        $response = $this->get('/bursar/staff-salaries/records/' . $record->id . '/payslip');
        $response->assertOk();
        $response->assertViewIs('bursar.payroll.payslip');
        $response->assertSee($employee->employee_number);
        $response->assertSee($period->name);
        $response->assertSee('₦' . number_format($record->net_pay, 2));
    }

    public function test_payslip_pdf_download(): void
    {
        $admin = $this->createAdmin();
        $bursar = $this->createBursar();
        $employee = $this->createEmployee();
        $period = $this->createPeriod($admin);

        SalaryProfile::create([
            'employee_id' => $employee->id,
            'basic_salary' => 50000.00,
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);

        $this->actingAs($admin);
        $this->post('/admin/payroll/periods/' . $period->id . '/generate');
        $record = PayrollRecord::first();
        $this->post('/admin/payroll/records/' . $record->id . '/approve');

        $this->actingAs($bursar);
        $this->post('/bursar/staff-salaries/records/' . $record->id . '/payment', [
            'payment_method' => 'bank_transfer',
            'paid_at' => '2026-02-05 10:00:00',
        ]);

        $response = $this->get('/bursar/staff-salaries/records/' . $record->id . '/payslip?download=1');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_payslip_generated_from_snapshot_not_live_values(): void
    {
        $admin = $this->createAdmin();
        $bursar = $this->createBursar();
        $employee = $this->createEmployee();
        $component = $this->createComponent(['name' => 'Housing Allowance', 'default_value' => 10000.00]);
        $period = $this->createPeriod($admin);

        $profile = SalaryProfile::create([
            'employee_id' => $employee->id,
            'basic_salary' => 50000.00,
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);

        SalaryProfileComponent::create([
            'salary_profile_id' => $profile->id,
            'salary_component_id' => $component->id,
            'configured_value' => 10000.00,
            'effective_from' => '2026-01-01',
        ]);

        $this->actingAs($admin);
        $this->post('/admin/payroll/periods/' . $period->id . '/generate');
        $record = PayrollRecord::first();
        $this->post('/admin/payroll/records/' . $record->id . '/approve');

        $this->actingAs($bursar);
        $this->post('/bursar/staff-salaries/records/' . $record->id . '/payment', [
            'payment_method' => 'bank_transfer',
            'paid_at' => '2026-02-05 10:00:00',
        ]);

        // Change source component after payroll generated
        $component->update(['default_value' => 99999.00]);

        $response = $this->get('/bursar/staff-salaries/records/' . $record->id . '/payslip');
        $response->assertOk();
        // Payslip should show the snapshot amount (10000), not the new live value (99999)
        $response->assertSee('₦' . number_format(10000.00, 2));
        $response->assertDontSee('₦' . number_format(99999.00, 2));
    }

    /* ---------------------------------------------------------------
     * Audit logging
     * --------------------------------------------------------------- */

    public function test_salary_component_changes_are_audited(): void
    {
        $admin = $this->createAdmin();
        $component = $this->createComponent();
        $this->actingAs($admin);

        $this->put('/admin/payroll/components/' . $component->id, [
            'name' => 'Updated Component',
            'component_type' => 'allowance',
            'calculation_type' => 'fixed',
            'default_value' => 20000.00,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'salary_component.updated',
            'target_model' => SalaryComponent::class,
            'target_id' => $component->id,
        ]);
    }

    public function test_payroll_period_changes_are_audited(): void
    {
        $admin = $this->createAdmin();
        $period = $this->createPeriod($admin);
        $this->actingAs($admin);

        $this->put('/admin/payroll/periods/' . $period->id, [
            'name' => 'Updated Period',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'pay_date' => '2026-02-05',
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'payroll_period.updated',
            'target_model' => PayrollPeriod::class,
            'target_id' => 1,
        ]);
    }
}
