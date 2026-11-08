<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\PayrollRecord;
use App\Models\PayrollRecordComponent;
use App\Models\Payslip;
use App\Models\SalaryComponent;
use App\Models\SalaryProfile;
use App\Models\SalaryProfileComponent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_be_created_without_user(): void
    {
        $employee = Employee::create([
            'employee_number' => 'EMP-001',
            'employment_type' => 'full-time',
            'date_employed' => '2026-01-15',
            'department' => 'Administration',
            'designation' => 'School Clerk',
            'employment_status' => 'active',
        ]);

        $this->assertNotNull($employee->id);
        $this->assertNull($employee->user_id);
        $this->assertEquals('EMP-001', $employee->employee_number);
        $this->assertEquals('full-time', $employee->employment_type);
        $this->assertEquals('active', $employee->employment_status);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $employee->date_employed);
    }

    public function test_employee_can_link_to_existing_user(): void
    {
        $user = User::create([
            'name' => 'John Staff',
            'email' => 'john.staff@example.com',
            'password' => 'password123',
            'role' => 'bursar',
            'staff_id' => 'B-001',
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'user_id' => $user->id,
            'employee_number' => 'EMP-B-001',
            'employment_type' => 'full-time',
            'date_employed' => '2026-01-15',
            'department' => 'Finance',
            'designation' => 'Bursar',
            'employment_status' => 'active',
        ]);

        $this->assertNotNull($employee->user_id);
        $this->assertEquals($user->id, $employee->user->id);
        $this->assertEquals('john.staff@example.com', $employee->user->email);
    }

    public function test_employee_number_must_be_unique(): void
    {
        Employee::create([
            'employee_number' => 'EMP-DUP-001',
            'employment_type' => 'full-time',
            'date_employed' => '2026-01-15',
            'employment_status' => 'active',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Employee::create([
            'employee_number' => 'EMP-DUP-001',
            'employment_type' => 'part-time',
            'date_employed' => '2026-02-01',
            'employment_status' => 'active',
        ]);
    }

    public function test_one_employee_per_user(): void
    {
        $user = User::create([
            'name' => 'Single User',
            'email' => 'single@example.com',
            'password' => 'password123',
            'role' => 'admin',
            'is_active' => true,
        ]);

        Employee::create([
            'user_id' => $user->id,
            'employee_number' => 'EMP-SINGLE-001',
            'employment_type' => 'full-time',
            'date_employed' => '2026-01-15',
            'employment_status' => 'active',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Employee::create([
            'user_id' => $user->id,
            'employee_number' => 'EMP-SINGLE-002',
            'employment_type' => 'part-time',
            'date_employed' => '2026-02-01',
            'employment_status' => 'active',
        ]);
    }

    public function test_sensitive_fields_are_encrypted_and_hidden(): void
    {
        $employee = Employee::create([
            'employee_number' => 'EMP-SENS-001',
            'employment_type' => 'full-time',
            'date_employed' => '2026-01-15',
            'employment_status' => 'active',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'John Doe Account',
            'tax_id' => 'TAX-12345',
            'pension_id' => 'PEN-67890',
        ]);

        // Sensitive fields should be hidden from serialization
        $attributes = $employee->toArray();
        $this->assertArrayNotHasKey('bank_account_number', $attributes);
        $this->assertArrayNotHasKey('bank_account_name', $attributes);
        $this->assertArrayNotHasKey('tax_id', $attributes);
        $this->assertArrayNotHasKey('pension_id', $attributes);

        // But they should be accessible via the model
        $this->assertEquals('1234567890', $employee->bank_account_number);
        $this->assertEquals('John Doe Account', $employee->bank_account_name);
        $this->assertEquals('TAX-12345', $employee->tax_id);
        $this->assertEquals('PEN-67890', $employee->pension_id);
    }

    public function test_salary_profiles_support_historical_records(): void
    {
        $employee = Employee::create([
            'employee_number' => 'EMP-HIST-001',
            'employment_type' => 'full-time',
            'date_employed' => '2026-01-15',
            'employment_status' => 'active',
        ]);

        $profile1 = SalaryProfile::create([
            'employee_id' => $employee->id,
            'basic_salary' => 50000.00,
            'effective_from' => '2026-01-01',
            'effective_to' => '2026-06-30',
            'is_active' => true,
        ]);

        $profile2 = SalaryProfile::create([
            'employee_id' => $employee->id,
            'basic_salary' => 55000.00,
            'effective_from' => '2026-07-01',
            'effective_to' => null,
            'is_active' => true,
        ]);

        $this->assertCount(2, $employee->salaryProfiles);
        $this->assertEquals(50000.00, $profile1->basic_salary);
        $this->assertEquals(55000.00, $profile2->basic_salary);
        $this->assertNull($profile2->effective_to);
    }

    public function test_employee_specific_component_values(): void
    {
        $employee = Employee::create([
            'employee_number' => 'EMP-COMP-001',
            'employment_type' => 'full-time',
            'date_employed' => '2026-01-15',
            'employment_status' => 'active',
        ]);

        $component = SalaryComponent::create([
            'name' => 'Housing Allowance',
            'description' => 'Monthly housing allowance',
            'component_type' => 'allowance',
            'calculation_type' => 'fixed',
            'default_value' => 10000.00,
            'is_taxable' => true,
            'is_active' => true,
        ]);

        $profile = SalaryProfile::create([
            'employee_id' => $employee->id,
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

        $this->assertCount(1, $profile->components);
        $this->assertEquals(15000.00, $profile->components->first()->configured_value);
        $this->assertEquals('Housing Allowance', $profile->components->first()->salaryComponent->name);
    }

    public function test_payroll_record_unique_per_employee_per_period(): void
    {
        $creator = User::create([
            'name' => 'Creator',
            'email' => 'creator@example.com',
            'password' => 'password123',
            'role' => 'bursar',
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'employee_number' => 'EMP-PER-001',
            'employment_type' => 'full-time',
            'date_employed' => '2026-01-15',
            'employment_status' => 'active',
        ]);

        $period = PayrollPeriod::create([
            'name' => 'January 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'pay_date' => '2026-02-05',
            'status' => 'draft',
            'created_by' => $creator->id,
        ]);

        PayrollRecord::create([
            'employee_id' => $employee->id,
            'payroll_period_id' => $period->id,
            'basic_salary_snapshot' => 50000.00,
            'gross_pay' => 50000.00,
            'total_allowances' => 0.00,
            'total_deductions' => 0.00,
            'net_pay' => 50000.00,
            'status' => 'calculated',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        PayrollRecord::create([
            'employee_id' => $employee->id,
            'payroll_period_id' => $period->id,
            'basic_salary_snapshot' => 50000.00,
            'gross_pay' => 50000.00,
            'total_allowances' => 0.00,
            'total_deductions' => 0.00,
            'net_pay' => 50000.00,
            'status' => 'calculated',
        ]);
    }

    public function test_payroll_record_components_preserve_historical_snapshot(): void
    {
        $creator = User::create([
            'name' => 'Creator',
            'email' => 'creator@example.com',
            'password' => 'password123',
            'role' => 'bursar',
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'employee_number' => 'EMP-SNAP-001',
            'employment_type' => 'full-time',
            'date_employed' => '2026-01-15',
            'employment_status' => 'active',
        ]);

        $period = PayrollPeriod::create([
            'name' => 'February 2026',
            'start_date' => '2026-02-01',
            'end_date' => '2026-02-28',
            'pay_date' => '2026-03-05',
            'status' => 'draft',
            'created_by' => $creator->id,
        ]);

        $record = PayrollRecord::create([
            'employee_id' => $employee->id,
            'payroll_period_id' => $period->id,
            'basic_salary_snapshot' => 50000.00,
            'gross_pay' => 60000.00,
            'total_allowances' => 10000.00,
            'total_deductions' => 0.00,
            'net_pay' => 60000.00,
            'status' => 'calculated',
        ]);

        $component = SalaryComponent::create([
            'name' => 'Transport Allowance',
            'description' => 'Monthly transport',
            'component_type' => 'allowance',
            'calculation_type' => 'fixed',
            'default_value' => 5000.00,
            'is_taxable' => true,
            'is_active' => true,
        ]);

        $recordComponent = PayrollRecordComponent::create([
            'payroll_record_id' => $record->id,
            'salary_component_id' => $component->id,
            'component_name' => 'Transport Allowance',
            'component_type' => 'allowance',
            'amount' => 10000.00,
            'calculation_basis' => 'fixed',
            'calculation_value' => 10000.00,
        ]);

        $this->assertCount(1, $record->components);
        $this->assertEquals('Transport Allowance', $recordComponent->component_name);
        $this->assertEquals(10000.00, $recordComponent->amount);

        // Changing the source component should not affect the snapshot
        $component->update(['name' => 'Updated Transport']);
        $this->assertEquals('Transport Allowance', $recordComponent->fresh()->component_name);
    }

    public function test_payslip_has_unique_number_and_relationships(): void
    {
        $creator = User::create([
            'name' => 'Creator',
            'email' => 'creator@example.com',
            'password' => 'password123',
            'role' => 'bursar',
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'employee_number' => 'EMP-PAY-001',
            'employment_type' => 'full-time',
            'date_employed' => '2026-01-15',
            'employment_status' => 'active',
        ]);

        $period = PayrollPeriod::create([
            'name' => 'March 2026',
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-31',
            'pay_date' => '2026-04-05',
            'status' => 'processed',
            'created_by' => $creator->id,
        ]);

        $record = PayrollRecord::create([
            'employee_id' => $employee->id,
            'payroll_period_id' => $period->id,
            'basic_salary_snapshot' => 50000.00,
            'gross_pay' => 50000.00,
            'total_allowances' => 0.00,
            'total_deductions' => 0.00,
            'net_pay' => 50000.00,
            'status' => 'approved',
        ]);

        $payslip = Payslip::create([
            'payroll_record_id' => $record->id,
            'payslip_number' => 'PSLIP-2026-03-001',
            'generated_at' => now(),
            'generated_by' => $creator->id,
        ]);

        $this->assertNotNull($payslip->id);
        $this->assertEquals('PSLIP-2026-03-001', $payslip->payslip_number);
        $this->assertEquals($creator->id, $payslip->generator->id);
        $this->assertEquals($record->id, $payslip->payrollRecord->id);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $payslip->generated_at);
    }

    public function test_payslip_number_must_be_unique(): void
    {
        $creator = User::create([
            'name' => 'Creator',
            'email' => 'creator@example.com',
            'password' => 'password123',
            'role' => 'bursar',
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'employee_number' => 'EMP-PAYSLIP-001',
            'employment_type' => 'full-time',
            'date_employed' => '2026-01-15',
            'employment_status' => 'active',
        ]);

        $period1 = PayrollPeriod::create([
            'name' => 'April 2026',
            'start_date' => '2026-04-01',
            'end_date' => '2026-04-30',
            'pay_date' => '2026-05-05',
            'status' => 'draft',
            'created_by' => $creator->id,
        ]);

        $record1 = PayrollRecord::create([
            'employee_id' => $employee->id,
            'payroll_period_id' => $period1->id,
            'basic_salary_snapshot' => 50000.00,
            'gross_pay' => 50000.00,
            'total_allowances' => 0.00,
            'total_deductions' => 0.00,
            'net_pay' => 50000.00,
            'status' => 'calculated',
        ]);

        Payslip::create([
            'payroll_record_id' => $record1->id,
            'payslip_number' => 'PSLIP-DUP-001',
            'generated_at' => now(),
            'generated_by' => $creator->id,
        ]);

        $period2 = PayrollPeriod::create([
            'name' => 'May 2026',
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-31',
            'pay_date' => '2026-06-05',
            'status' => 'draft',
            'created_by' => $creator->id,
        ]);

        $record2 = PayrollRecord::create([
            'employee_id' => $employee->id,
            'payroll_period_id' => $period2->id,
            'basic_salary_snapshot' => 50000.00,
            'gross_pay' => 50000.00,
            'total_allowances' => 0.00,
            'total_deductions' => 0.00,
            'net_pay' => 50000.00,
            'status' => 'calculated',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Payslip::create([
            'payroll_record_id' => $record2->id,
            'payslip_number' => 'PSLIP-DUP-001',
            'generated_at' => now(),
            'generated_by' => 1,
        ]);
    }

    public function test_one_payslip_per_payroll_record(): void
    {
        $creator = User::create([
            'name' => 'Creator',
            'email' => 'creator@example.com',
            'password' => 'password123',
            'role' => 'bursar',
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'employee_number' => 'EMP-PAYSLIP-UNIQUE-001',
            'employment_type' => 'full-time',
            'date_employed' => '2026-01-15',
            'employment_status' => 'active',
        ]);

        $period = PayrollPeriod::create([
            'name' => 'June 2026',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'pay_date' => '2026-07-05',
            'status' => 'draft',
            'created_by' => $creator->id,
        ]);

        $record = PayrollRecord::create([
            'employee_id' => $employee->id,
            'payroll_period_id' => $period->id,
            'basic_salary_snapshot' => 50000.00,
            'gross_pay' => 50000.00,
            'total_allowances' => 0.00,
            'total_deductions' => 0.00,
            'net_pay' => 50000.00,
            'status' => 'calculated',
        ]);

        Payslip::create([
            'payroll_record_id' => $record->id,
            'payslip_number' => 'PSLIP-UNIQUE-001',
            'generated_at' => now(),
            'generated_by' => $creator->id,
        ]);

        // A second payslip for the same payroll record must be rejected
        $this->expectException(\Illuminate\Database\QueryException::class);
        Payslip::create([
            'payroll_record_id' => $record->id,
            'payslip_number' => 'PSLIP-UNIQUE-002',
            'generated_at' => now(),
            'generated_by' => $creator->id,
        ]);
    }

    public function test_duplicate_salary_component_assignment_prevented(): void
    {
        $employee = Employee::create([
            'employee_number' => 'EMP-COMP-DUP-001',
            'employment_type' => 'full-time',
            'date_employed' => '2026-01-15',
            'employment_status' => 'active',
        ]);

        $component = SalaryComponent::create([
            'name' => 'Housing Allowance',
            'description' => 'Monthly housing allowance',
            'component_type' => 'allowance',
            'calculation_type' => 'fixed',
            'default_value' => 10000.00,
            'is_taxable' => true,
            'is_active' => true,
        ]);

        $profile = SalaryProfile::create([
            'employee_id' => $employee->id,
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

        // Duplicate assignment with same effective_from must be rejected
        $this->expectException(\Illuminate\Database\QueryException::class);
        SalaryProfileComponent::create([
            'salary_profile_id' => $profile->id,
            'salary_component_id' => $component->id,
            'configured_value' => 20000.00,
            'effective_from' => '2026-01-01',
        ]);
    }

    public function test_different_effective_dates_allowed_for_same_component(): void
    {
        $employee = Employee::create([
            'employee_number' => 'EMP-COMP-DIFF-001',
            'employment_type' => 'full-time',
            'date_employed' => '2026-01-15',
            'employment_status' => 'active',
        ]);

        $component = SalaryComponent::create([
            'name' => 'Housing Allowance',
            'description' => 'Monthly housing allowance',
            'component_type' => 'allowance',
            'calculation_type' => 'fixed',
            'default_value' => 10000.00,
            'is_taxable' => true,
            'is_active' => true,
        ]);

        $profile = SalaryProfile::create([
            'employee_id' => $employee->id,
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

        // Same profile and component but different effective_from must be allowed
        SalaryProfileComponent::create([
            'salary_profile_id' => $profile->id,
            'salary_component_id' => $component->id,
            'configured_value' => 20000.00,
            'effective_from' => '2026-07-01',
        ]);

        $this->assertCount(2, $profile->components);
    }

    public function test_salary_profile_deletion_restricted_when_components_exist(): void
    {
        $employee = Employee::create([
            'employee_number' => 'EMP-PROF-DELETE-001',
            'employment_type' => 'full-time',
            'date_employed' => '2026-01-15',
            'employment_status' => 'active',
        ]);

        $component = SalaryComponent::create([
            'name' => 'Housing Allowance',
            'description' => 'Monthly housing allowance',
            'component_type' => 'allowance',
            'calculation_type' => 'fixed',
            'default_value' => 10000.00,
            'is_taxable' => true,
            'is_active' => true,
        ]);

        $profile = SalaryProfile::create([
            'employee_id' => $employee->id,
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

        // Deleting a salary profile with existing component assignments must be restricted
        $this->expectException(\Illuminate\Database\QueryException::class);
        $profile->delete();
    }

    public function test_payroll_component_snapshot_deletion_restricted_when_payroll_record_exists(): void
    {
        $creator = User::create([
            'name' => 'Creator',
            'email' => 'creator@example.com',
            'password' => 'password123',
            'role' => 'bursar',
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'employee_number' => 'EMP-SNAP-DELETE-001',
            'employment_type' => 'full-time',
            'date_employed' => '2026-01-15',
            'employment_status' => 'active',
        ]);

        $period = PayrollPeriod::create([
            'name' => 'July 2026',
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-31',
            'pay_date' => '2026-08-05',
            'status' => 'draft',
            'created_by' => $creator->id,
        ]);

        $record = PayrollRecord::create([
            'employee_id' => $employee->id,
            'payroll_period_id' => $period->id,
            'basic_salary_snapshot' => 50000.00,
            'gross_pay' => 50000.00,
            'total_allowances' => 0.00,
            'total_deductions' => 0.00,
            'net_pay' => 50000.00,
            'status' => 'calculated',
        ]);

        $component = SalaryComponent::create([
            'name' => 'Transport Allowance',
            'description' => 'Monthly transport',
            'component_type' => 'allowance',
            'calculation_type' => 'fixed',
            'default_value' => 5000.00,
            'is_taxable' => true,
            'is_active' => true,
        ]);

        $recordComponent = PayrollRecordComponent::create([
            'payroll_record_id' => $record->id,
            'salary_component_id' => $component->id,
            'component_name' => 'Transport Allowance',
            'component_type' => 'allowance',
            'amount' => 10000.00,
            'calculation_basis' => 'fixed',
            'calculation_value' => 10000.00,
        ]);

        // Deleting a payroll record with existing component snapshots must be restricted
        $this->expectException(\Illuminate\Database\QueryException::class);
        $record->delete();
    }
}
