<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\FeeType;
use App\Models\Payment;
use App\Models\PaymentReversal;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\Term;
use App\Models\User;
use App\Services\FeeService;
use App\Models\FeePackage;
use App\Models\FeeComponent;
use App\Models\StudentFeeComponentSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_fee_package_assignment_rejects_term_from_different_academic_session(): void
{
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    $sessionOne = AcademicSession::create([
        'name' => '2026/2027',
        'start_date' => '2026-09-01',
        'end_date' => '2027-07-31',
        'is_current' => true,
    ]);

    $sessionTwo = AcademicSession::create([
        'name' => '2027/2028',
        'start_date' => '2027-09-01',
        'end_date' => '2028-07-31',
        'is_current' => false,
    ]);

    $term = Term::create([
        'academic_session_id' => $sessionOne->id,
        'name' => 'First Term',
        'start_date' => '2026-09-01',
        'end_date' => '2026-12-20',
        'is_current' => true,
    ]);

    $class = SchoolClass::create(['name' => 'JSS 1']);

    $package = FeePackage::create([
        'name' => 'Standard Package',
        'description' => 'Regression test package',
        'is_active' => true,
        'created_by' => $admin->id,
    ]);

    FeeComponent::create([
        'fee_package_id' => $package->id,
        'name' => 'Tuition',
        'amount' => 50000,
        'sort_order' => 0,
    ]);

    $response = $this->post(route('admin.finance.packages.assign'), [
        'fee_package_id' => $package->id,
        'class_id' => $class->id,
        'term_id' => $term->id,
        'academic_session_id' => $sessionTwo->id,
    ]);

    $response->assertSessionHasErrors('term_id');

    $this->assertDatabaseCount('fee_package_assignments', 0);
    $this->assertDatabaseCount('student_fees', 0);
}

    public function test_admin_can_create_fee_types(): void
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

        // Student fees are auto-created when fee type is created
        $this->assertDatabaseHas('student_fees', ['student_id' => $student->id, 'fee_type_id' => $feeType->id]);
    }

    public function test_admin_cannot_record_payments_on_bursar_endpoint(): void
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

        // Admin trying to access Bursar payment endpoint should be forbidden
        $response = $this->post('/bursar/payments', [
            'student_fee_id' => $studentFee->id,
            'amount_paid' => '1500.00',
            'payment_method' => 'cash',
            'payment_date' => '2026-07-15',
        ]);

        $response->assertForbidden();
    }

    public function test_admin_cannot_access_bursar_receipt_endpoint(): void
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

        $payment = Payment::create([
            'student_fee_id' => $studentFee->id,
            'receipt_number' => 'RCPT-001',
            'amount_paid' => 1500.00,
            'payment_method' => 'cash',
            'payment_date' => '2026-07-15',
            'recorded_by' => $studentUser->id,
        ]);

        // Admin trying to access Bursar receipt endpoint should be forbidden
        $response = $this->get("/bursar/payments/{$payment->id}/receipt");
        $response->assertForbidden();
    }

    public function test_admin_cannot_access_bursar_student_fees(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get('/bursar/student-fees');
        $response->assertForbidden();
    }

    public function test_admin_cannot_access_bursar_fee_collections(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get('/bursar/fee-collections');
        $response->assertForbidden();
    }

    public function test_payment_reference_and_notes_are_persisted_on_bursar_endpoint(): void
    {
        $bursar = User::factory()->create(['role' => 'bursar']);
        $this->actingAs($bursar);

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

        $paymentResponse = $this->post('/bursar/payments', [
            'student_fee_id' => $studentFee->id,
            'receipt_number' => 'RCPT-REF-001',
            'amount_paid' => '1500.00',
            'payment_method' => 'bank',
            'payment_date' => '2026-07-15',
            'reference' => 'Bank Teller #4521',
            'notes' => 'Paid via direct bank transfer.',
        ]);

        $paymentResponse->assertRedirect(route('bursar.student-fees.show', $studentFee))->assertSessionHas('status');
        $this->assertDatabaseHas('payments', [
            'student_fee_id' => $studentFee->id,
            'receipt_number' => 'RCPT-REF-001',
            'reference' => 'Bank Teller #4521',
            'notes' => 'Paid via direct bank transfer.',
        ]);
    }

    public function test_payment_with_future_date_is_rejected_on_bursar_endpoint(): void
    {
        $bursar = User::factory()->create(['role' => 'bursar']);
        $this->actingAs($bursar);

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

        $paymentResponse = $this->post('/bursar/payments', [
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

        $response = $this->post('/admin/finance/fee-types', [
            'name' => 'Tuition Fee',
            'amount' => '-500.00',
            'term_id' => $term->id,
            'class_id' => $class->id,
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseMissing('fee_types', [
            'name' => 'Tuition Fee',
            'amount' => -500.00,
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

        // Same name on a DIFFERENT class should be allowed
        $class2 = SchoolClass::create(['name' => 'JSS 7']);

        $this->post('/admin/finance/fee-types', [
            'name' => 'Tuition Fee',
            'amount' => '1000.00',
            'term_id' => $term->id,
            'class_id' => $class2->id,
        ]);

        $this->assertDatabaseCount('fee_types', 2);
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

    public function test_partial_payment_leaves_fee_status_as_partial_on_bursar_endpoint(): void
    {
        $bursar = User::factory()->create(['role' => 'bursar']);
        $this->actingAs($bursar);

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
        $paymentResponse = $this->post('/bursar/payments', [
            'student_fee_id' => $studentFee->id,
            'amount_paid' => '500.00',
            'payment_method' => 'cash',
            'payment_date' => '2026-07-15',
        ]);

        $paymentResponse->assertRedirect(route('bursar.student-fees.show', $studentFee))->assertSessionHas('status');
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
    public function test_finance_summary_counts_fees_with_only_reversed_payments_as_unpaid(): void
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

    $studentUser = User::factory()->create();

    $student = Student::create([
        'user_id' => $studentUser->id,
        'class_id' => $class->id,
        'admission_no' => 'ADM-REVERSED-001',
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
        'status' => 'paid',
    ]);

    $payment = Payment::create([
        'student_fee_id' => $studentFee->id,
        'receipt_number' => 'RCPT-REVERSED-001',
        'amount_paid' => 1000.00,
        'payment_method' => 'cash',
        'payment_date' => '2026-07-15',
        'recorded_by' => $studentUser->id,
    ]);

    PaymentReversal::create([
        'payment_id' => $payment->id,
        'reversed_by' => $studentUser->id,
        'reason' => 'Test reversal',
        'amount_reversed' => 1000.00,
        'reversed_at' => now(),
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

    public function test_payment_exceeding_outstanding_balance_is_rejected(): void
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

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM-PAY-001',
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

        $this->expectException(\InvalidArgumentException::class);
        $feeService->recordPayment($studentFee, [
            'amount_paid' => 1500.00,
            'payment_method' => 'cash',
            'payment_date' => '2026-09-01',
        ], $studentUser);
    }

    public function test_concurrent_payments_together_exceed_balance_second_is_rejected(): void
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

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM-CONC-001',
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

        // First payment of 600 (within balance)
        $payment1 = $feeService->recordPayment($studentFee, [
            'amount_paid' => 600.00,
            'payment_method' => 'cash',
            'payment_date' => '2026-09-01',
        ], $studentUser);

        $this->assertNotNull($payment1->id);
        $this->assertEquals(600.00, $payment1->amount_paid);
        $this->assertEquals('partial', $studentFee->fresh()->status);

        // Second payment of 500 would make total 1100 > 1000 balance, should be rejected
        $this->expectException(\InvalidArgumentException::class);
        $feeService->recordPayment($studentFee, [
            'amount_paid' => 500.00,
            'payment_method' => 'cash',
            'payment_date' => '2026-09-02',
        ], $studentUser);

        // Verify the second payment was not recorded (rollback)
        $this->assertEquals(1, $studentFee->fresh()->payments()->count());
        $this->assertEquals(600.00, $studentFee->fresh()->payments()->sum('amount_paid'));
    }

    public function test_payment_reversal_then_repayment_restores_balance(): void
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

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM-REV-001',
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

        // Record payment of 1000 (fully paid)
        $payment = $feeService->recordPayment($studentFee, [
            'amount_paid' => 1000.00,
            'payment_method' => 'cash',
            'payment_date' => '2026-09-01',
        ], $studentUser);

        $this->assertEquals('paid', $studentFee->fresh()->status);

        // Reverse the payment
        $reversal = $feeService->reversePayment($payment, 'Test reversal reason', $studentUser);

        $this->assertNotNull($reversal->id);
        $this->assertEquals(1000.00, $reversal->amount_reversed);
        $this->assertEquals('unpaid', $studentFee->fresh()->status);

        // Re-payment after reversal should work
        $payment2 = $feeService->recordPayment($studentFee, [
            'amount_paid' => 500.00,
            'payment_method' => 'cash',
            'payment_date' => '2026-09-02',
        ], $studentUser);

        $this->assertNotNull($payment2->id);
        $this->assertEquals(500.00, $payment2->amount_paid);
        $this->assertEquals('partial', $studentFee->fresh()->status);
    }

    public function test_already_reversed_payment_cannot_be_reversed_again(): void
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

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM-DOUBLE-001',
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

        $payment = $feeService->recordPayment($studentFee, [
            'amount_paid' => 1000.00,
            'payment_method' => 'cash',
            'payment_date' => '2026-09-01',
        ], $studentUser);

        // First reversal
        $feeService->reversePayment($payment, 'First reversal', $studentUser);

        // Second reversal should fail
        $this->expectException(\InvalidArgumentException::class);
        $feeService->reversePayment($payment, 'Second reversal', $studentUser);
    }

    public function test_fee_package_cannot_be_deleted_with_generated_student_fees(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $package = FeePackage::create([
            'name' => 'Deletable Package',
            'description' => 'Test package',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        FeeComponent::create([
            'fee_package_id' => $package->id,
            'name' => 'Tuition',
            'amount' => 500.00,
            'sort_order' => 1,
        ]);

        // Create a student fee referencing this package
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

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM-DEL-001',
        ]);

        $feeType = FeeType::create([
            'name' => 'Package Fee',
            'amount' => 500.00,
            'term_id' => $term->id,
            'class_id' => $class->id,
        ]);

        StudentFee::create([
            'student_id' => $student->id,
            'fee_type_id' => $feeType->id,
            'fee_source_type' => 'fee_package',
            'fee_package_id' => $package->id,
            'term_id' => $term->id,
            'amount_expected' => 500.00,
            'status' => 'unpaid',
        ]);

        $response = $this->delete(route('admin.finance.packages.destroy', $package));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('fee_packages', ['id' => $package->id]);
    }

    public function test_fee_package_cannot_update_components_with_existing_snapshots(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

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

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM-SNAP-001',
        ]);

        $package = FeePackage::create([
            'name' => 'Snapshot Package',
            'description' => 'Test',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $component = FeeComponent::create([
            'fee_package_id' => $package->id,
            'name' => 'Tuition',
            'amount' => 500.00,
            'sort_order' => 1,
        ]);

        // Create student fee with snapshot referencing this component
        $feeType = FeeType::create([
            'name' => 'Snapshot Fee',
            'amount' => 500.00,
            'term_id' => $term->id,
            'class_id' => $class->id,
        ]);

        $studentFee = StudentFee::create([
            'student_id' => $student->id,
            'fee_type_id' => $feeType->id,
            'fee_source_type' => 'fee_package',
            'fee_package_id' => $package->id,
            'term_id' => $term->id,
            'amount_expected' => 500.00,
            'status' => 'unpaid',
        ]);

        StudentFeeComponentSnapshot::create([
            'student_fee_id' => $studentFee->id,
            'fee_component_id' => $component->id,
            'component_name' => 'Tuition',
            'component_amount' => 500.00,
            'component_description' => null,
        ]);

        // Attempt to update package components (should fail due to snapshot reference)
        $response = $this->put(route('admin.finance.packages.update', $package), [
            'name' => 'Updated Package',
            'description' => 'Updated',
            'is_active' => true,
            'components' => [
                ['name' => 'New Tuition', 'amount' => 600.00, 'description' => ''],
            ],
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseHas('fee_components', ['id' => $component->id, 'name' => 'Tuition']);
    }
}