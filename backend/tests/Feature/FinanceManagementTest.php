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
use App\Services\FeeService;
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
            'payment_date' => '2026-07-15',
        ]);

        $paymentResponse->assertRedirect('/admin/finance');
        $this->assertDatabaseHas('payments', ['student_fee_id' => $studentFee->id, 'receipt_number' => 'RCPT-001']);
        $this->assertDatabaseHas('student_fees', ['id' => $studentFee->id, 'status' => 'paid']);
    }

    public function test_payment_reference_and_notes_are_persisted(): void
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

        $class = SchoolClass::create(['name' => 'JSS 3']);
        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM103',
        ]);

        $feeType = FeeType::create([
            'name' => 'Tuition Fee',
            'amount' => 1500.00,
            'term_id' => $term->id,
            'class_id' => $class->id,
        ]);

        $studentFee = StudentFee::create([
            'student_id' => $student->id,
            'fee_type_id' => $feeType->id,
            'term_id' => $term->id,
            'amount_expected' => 1500.00,
            'status' => 'unpaid',
        ]);

        $paymentResponse = $this->post('/admin/finance/payments', [
            'student_fee_id' => $studentFee->id,
            'receipt_number' => 'RCPT-REF-001',
            'amount_paid' => '1500.00',
            'payment_method' => 'bank',
            'payment_date' => '2026-07-15',
            'reference' => 'Bank Teller #4521',
            'notes' => 'Paid via direct bank transfer.',
        ]);

        $paymentResponse->assertRedirect('/admin/finance');
        $this->assertDatabaseHas('payments', [
            'student_fee_id' => $studentFee->id,
            'receipt_number' => 'RCPT-REF-001',
            'reference' => 'Bank Teller #4521',
            'notes' => 'Paid via direct bank transfer.',
        ]);
    }

    public function test_payment_with_future_date_is_rejected(): void
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

        $class = SchoolClass::create(['name' => 'JSS 4']);
        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM104',
        ]);

        $feeType = FeeType::create([
            'name' => 'Tuition Fee',
            'amount' => 1500.00,
            'term_id' => $term->id,
            'class_id' => $class->id,
        ]);

        $studentFee = StudentFee::create([
            'student_id' => $student->id,
            'fee_type_id' => $feeType->id,
            'term_id' => $term->id,
            'amount_expected' => 1500.00,
            'status' => 'unpaid',
        ]);

        $futureDate = now()->addDay()->format('Y-m-d');

        $paymentResponse = $this->post('/admin/finance/payments', [
            'student_fee_id' => $studentFee->id,
            'receipt_number' => 'RCPT-FUTURE',
            'amount_paid' => '1500.00',
            'payment_method' => 'cash',
            'payment_date' => $futureDate,
        ]);

        $paymentResponse->assertSessionHasErrors('payment_date');
        $this->assertDatabaseMissing('payments', ['receipt_number' => 'RCPT-FUTURE']);
    }

    public function test_student_fee_with_negative_amount_is_rejected(): void
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

        $class = SchoolClass::create(['name' => 'JSS 5']);
        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM105',
        ]);

        $feeType = FeeType::create([
            'name' => 'Tuition Fee',
            'amount' => 1500.00,
            'term_id' => $term->id,
            'class_id' => $class->id,
        ]);

        $studentFeeResponse = $this->post('/admin/finance/student-fees', [
            'student_id' => $student->id,
            'fee_type_id' => $feeType->id,
            'term_id' => $term->id,
            'amount_expected' => '-500.00',
        ]);

        $studentFeeResponse->assertSessionHasErrors('amount_expected');
        $this->assertDatabaseMissing('student_fees', [
            'student_id' => $student->id,
            'fee_type_id' => $feeType->id,
            'amount_expected' => -500.00,
        ]);
    }

    public function test_fee_type_duplicate_name_same_term_and_class_is_rejected(): void
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

        $class = SchoolClass::create(['name' => 'JSS 6']);

        $this->post('/admin/finance/fee-types', [
            'name' => 'Tuition Fee',
            'amount' => '1000.00',
            'term_id' => $term->id,
            'class_id' => $class->id,
        ]);

        $duplicateResponse = $this->post('/admin/finance/fee-types', [
            'name' => 'Tuition Fee',
            'amount' => '1000.00',
            'term_id' => $term->id,
            'class_id' => $class->id,
        ]);

        $duplicateResponse->assertSessionHasErrors('name');
        $this->assertDatabaseCount('fee_types', 1);
    }

    public function test_fee_type_same_name_different_term_is_allowed(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);

        $term1 = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
        ]);

        $term2 = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'Second Term',
            'start_date' => '2027-01-01',
            'end_date' => '2027-04-30',
            'is_current' => false,
        ]);

        $class = SchoolClass::create(['name' => 'JSS 7']);

        $this->post('/admin/finance/fee-types', [
            'name' => 'Tuition Fee',
            'amount' => '1000.00',
            'term_id' => $term1->id,
            'class_id' => $class->id,
        ]);

        $this->post('/admin/finance/fee-types', [
            'name' => 'Tuition Fee',
            'amount' => '1200.00',
            'term_id' => $term2->id,
            'class_id' => $class->id,
        ]);

        $this->assertDatabaseCount('fee_types', 2);
    }

    public function test_fee_type_same_name_different_class_is_allowed(): void
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

        $class1 = SchoolClass::create(['name' => 'JSS 8']);
        $class2 = SchoolClass::create(['name' => 'JSS 9']);

        $this->post('/admin/finance/fee-types', [
            'name' => 'Tuition Fee',
            'amount' => '1000.00',
            'term_id' => $term->id,
            'class_id' => $class1->id,
        ]);

        $this->post('/admin/finance/fee-types', [
            'name' => 'Tuition Fee',
            'amount' => '1200.00',
            'term_id' => $term->id,
            'class_id' => $class2->id,
        ]);

        $this->assertDatabaseCount('fee_types', 2);
    }

    public function test_partial_payment_leaves_fee_status_as_partial(): void
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

        $class = SchoolClass::create(['name' => 'JSS 2']);
        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM102',
        ]);

        $feeType = FeeType::create([
            'name' => 'Tuition Fee',
            'amount' => 1500.00,
            'term_id' => $term->id,
            'class_id' => $class->id,
        ]);

        $studentFee = StudentFee::create([
            'student_id' => $student->id,
            'fee_type_id' => $feeType->id,
            'term_id' => $term->id,
            'amount_expected' => 1500.00,
            'status' => 'unpaid',
        ]);

        // Pay only part of the expected amount.
        $paymentResponse = $this->post('/admin/finance/payments', [
            'student_fee_id' => $studentFee->id,
            'amount_paid' => '500.00',
            'payment_method' => 'cash',
            'payment_date' => '2026-07-15',
        ]);

        $paymentResponse->assertRedirect('/admin/finance');
        $this->assertDatabaseHas('student_fees', ['id' => $studentFee->id, 'status' => 'partial']);
    }

    public function test_finance_summary_with_no_payments(): void
    {
        $feeService = new FeeService;

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
            'admission_no' => 'ADM106',
        ]);

        $feeType = FeeType::create([
            'name' => 'Tuition Fee',
            'amount' => 1000.00,
            'term_id' => $term->id,
            'class_id' => $class->id,
        ]);

        StudentFee::create([
            'student_id' => $student->id,
            'fee_type_id' => $feeType->id,
            'term_id' => $term->id,
            'amount_expected' => 1000.00,
            'status' => 'unpaid',
        ]);

        $summary = $feeService->financeSummary();

        $this->assertEquals(1000.00, $summary['expected']);
        $this->assertEquals(0.00, $summary['collected']);
        $this->assertEquals(1000.00, $summary['outstanding']);
        $this->assertEquals(0.0, $summary['collection_rate']);
        $this->assertEquals(0, $summary['paid']);
        $this->assertEquals(0, $summary['partial']);
        $this->assertEquals(1, $summary['unpaid']);
    }

    public function test_finance_summary_with_full_payment(): void
    {
        $feeService = new FeeService;

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
            'admission_no' => 'ADM107',
        ]);

        $feeType = FeeType::create([
            'name' => 'Tuition Fee',
            'amount' => 1000.00,
            'term_id' => $term->id,
            'class_id' => $class->id,
        ]);

        $studentFee = StudentFee::create([
            'student_id' => $student->id,
            'fee_type_id' => $feeType->id,
            'term_id' => $term->id,
            'amount_expected' => 1000.00,
            'status' => 'unpaid',
        ]);

        Payment::create([
            'student_fee_id' => $studentFee->id,
            'receipt_number' => 'RCPT-002',
            'amount_paid' => 1000.00,
            'payment_method' => 'cash',
            'payment_date' => '2026-07-15',
            'recorded_by' => $studentUser->id,
        ]);

        $studentFee->update(['status' => 'paid']);

        $summary = $feeService->financeSummary();

        $this->assertEquals(1000.00, $summary['expected']);
        $this->assertEquals(1000.00, $summary['collected']);
        $this->assertEquals(0.00, $summary['outstanding']);
        $this->assertEquals(100.0, $summary['collection_rate']);
        $this->assertEquals(1, $summary['paid']);
        $this->assertEquals(0, $summary['partial']);
        $this->assertEquals(0, $summary['unpaid']);
    }

    public function test_finance_summary_with_partial_payment(): void
    {
        $feeService = new FeeService;

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
            'admission_no' => 'ADM108',
        ]);

        $feeType = FeeType::create([
            'name' => 'Tuition Fee',
            'amount' => 1000.00,
            'term_id' => $term->id,
            'class_id' => $class->id,
        ]);

        $studentFee = StudentFee::create([
            'student_id' => $student->id,
            'fee_type_id' => $feeType->id,
            'term_id' => $term->id,
            'amount_expected' => 1000.00,
            'status' => 'unpaid',
        ]);

        Payment::create([
            'student_fee_id' => $studentFee->id,
            'receipt_number' => 'RCPT-003',
            'amount_paid' => 500.00,
            'payment_method' => 'cash',
            'payment_date' => '2026-07-15',
            'recorded_by' => $studentUser->id,
        ]);

        $studentFee->update(['status' => 'partial']);

        $summary = $feeService->financeSummary();

        $this->assertEquals(1000.00, $summary['expected']);
        $this->assertEquals(500.00, $summary['collected']);
        $this->assertEquals(500.00, $summary['outstanding']);
        $this->assertEquals(50.0, $summary['collection_rate']);
        $this->assertEquals(0, $summary['paid']);
        $this->assertEquals(1, $summary['partial']);
        $this->assertEquals(0, $summary['unpaid']);
    }

    public function test_finance_summary_aggregates_multiple_fees_and_students(): void
    {
        $feeService = new FeeService;

        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-08-31',
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

        $student1User = User::factory()->create();
        $student1 = Student::create([
            'user_id' => $student1User->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM109',
        ]);

        $student2User = User::factory()->create();
        $student2 = Student::create([
            'user_id' => $student2User->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM110',
        ]);

        $feeType = FeeType::create([
            'name' => 'Tuition Fee',
            'amount' => 1000.00,
            'term_id' => $term->id,
            'class_id' => $class->id,
        ]);

        $feeType2 = FeeType::create([
            'name' => 'Library Fee',
            'amount' => 500.00,
            'term_id' => $term->id,
            'class_id' => $class->id,
        ]);

        // Fee 1: No payments (unpaid)
        StudentFee::create([
            'student_id' => $student1->id,
            'fee_type_id' => $feeType->id,
            'term_id' => $term->id,
            'amount_expected' => 1000.00,
            'status' => 'unpaid',
        ]);

        // Fee 2: Full payment (paid)
        $studentFee2 = StudentFee::create([
            'student_id' => $student2->id,
            'fee_type_id' => $feeType->id,
            'term_id' => $term->id,
            'amount_expected' => 1000.00,
            'status' => 'unpaid',
        ]);

        Payment::create([
            'student_fee_id' => $studentFee2->id,
            'receipt_number' => 'RCPT-004',
            'amount_paid' => 1000.00,
            'payment_method' => 'cash',
            'payment_date' => '2026-07-15',
            'recorded_by' => $student1User->id,
        ]);

        $studentFee2->update(['status' => 'paid']);

        // Fee 3: Partial payment (partial)
        $studentFee3 = StudentFee::create([
            'student_id' => $student1->id,
            'fee_type_id' => $feeType2->id,
            'term_id' => $term->id,
            'amount_expected' => 500.00,
            'status' => 'unpaid',
        ]);

        Payment::create([
            'student_fee_id' => $studentFee3->id,
            'receipt_number' => 'RCPT-005',
            'amount_paid' => 200.00,
            'payment_method' => 'cash',
            'payment_date' => '2026-07-15',
            'recorded_by' => $student1User->id,
        ]);

        $studentFee3->update(['status' => 'partial']);

        $summary = $feeService->financeSummary();

        $this->assertEquals(2500.00, $summary['expected']);
        $this->assertEquals(1200.00, $summary['collected']);
        $this->assertEquals(1300.00, $summary['outstanding']);
        $this->assertEquals(round(48.0, 1), $summary['collection_rate']);
        $this->assertEquals(1, $summary['paid']);
        $this->assertEquals(1, $summary['partial']);
        $this->assertEquals(1, $summary['unpaid']);
    }

    public function test_finance_summary_with_no_fees_returns_zeros(): void
    {
        $feeService = new FeeService;

        $summary = $feeService->financeSummary();

        $this->assertEquals(0.00, $summary['expected']);
        $this->assertEquals(0.00, $summary['collected']);
        $this->assertEquals(0.00, $summary['outstanding']);
        $this->assertEquals(0.0, $summary['collection_rate']);
        $this->assertEquals(0, $summary['paid']);
        $this->assertEquals(0, $summary['partial']);
        $this->assertEquals(0, $summary['unpaid']);
    }
}
