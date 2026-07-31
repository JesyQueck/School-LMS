<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\FeeType;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_fee_types_and_record_payments(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);

        $term = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
        ]);

        $class = SchoolClass::create(['name' => 'JSS 1']);
        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM101',
        ]);

        $response = $this->post('/admin/finance/fee-types', [
            'name' => 'Tuition Fee',
            'amount' => '1500.00',
            'term_id' => $term->id,
            'class_id' => $class->id,
        ]);

        $response->assertRedirect('/admin/finance');
        $this->assertDatabaseHas('fee_types', ['name' => 'Tuition Fee', 'amount' => '1500.00']);

        $feeType = FeeType::where('name', 'Tuition Fee')->firstOrFail();

        $studentFeeResponse = $this->post('/admin/finance/student-fees', [
            'student_id' => $student->id,
            'fee_type_id' => $feeType->id,
            'term_id' => $term->id,
            'amount_expected' => '1500.00',
        ]);

        $studentFeeResponse->assertRedirect('/admin/finance');
        $this->assertDatabaseHas('student_fees', ['student_id' => $student->id, 'fee_type_id' => $feeType->id]);

        $studentFee = StudentFee::where('student_id', $student->id)->firstOrFail();

        $paymentResponse = $this->post('/admin/finance/payments', [
            'student_fee_id' => $studentFee->id,
            'receipt_number' => 'RCPT-001',
            'amount_paid' => '1500.00',
            'payment_method' => 'cash',
            'payment_date' => '2026-09-15',
        ]);

        $paymentResponse->assertRedirect('/admin/finance');
        $this->assertDatabaseHas('payments', ['student_fee_id' => $studentFee->id, 'receipt_number' => 'RCPT-001']);
        $this->assertDatabaseHas('student_fees', ['id' => $studentFee->id, 'status' => 'paid']);
    }
}
