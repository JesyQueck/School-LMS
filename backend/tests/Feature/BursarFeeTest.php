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

class BursarFeeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bursar = User::factory()->create([
            'email' => 'bursar@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'bursar',
            'is_active' => true,
            'needs_password_change' => false,
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);

        $this->term = Term::create([
            'academic_session_id' => $this->session->id,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
        ]);

        $this->class = SchoolClass::create(['name' => 'JSS 1']);

        $this->studentUser = User::factory()->create();
        $this->student = Student::create([
            'user_id' => $this->studentUser->id,
            'class_id' => $this->class->id,
            'admission_no' => 'ADM101',
        ]);

        $this->feeType = FeeType::create([
            'name' => 'Tuition Fee',
            'amount' => 1500.00,
            'term_id' => $this->term->id,
            'class_id' => $this->class->id,
        ]);

        $this->studentFee = StudentFee::create([
            'student_id' => $this->student->id,
            'fee_type_id' => $this->feeType->id,
            'term_id' => $this->term->id,
            'amount_expected' => 1500.00,
            'status' => 'unpaid',
        ]);
    }

    public function test_bursar_can_access_student_fees_page(): void
    {
        $this->actingAs($this->bursar);

        $response = $this->get('/bursar/student-fees');

        $response->assertOk();
        $response->assertViewIs('bursar.student-fees');
    }

    public function test_bursar_can_record_full_payment(): void
    {
        $this->actingAs($this->bursar);

        $response = $this->post('/bursar/payments', [
            'student_fee_id' => $this->studentFee->id,
            'amount_paid' => '1500.00',
            'payment_method' => 'cash',
            'payment_date' => '2026-07-15',
            'reference' => 'REF-001',
            'notes' => 'Full payment',
        ]);

        $response->assertRedirect('/bursar/student-fees');
        $response->assertSessionHas('status', 'Payment recorded for '.$this->student->full_name.'.');

        $this->assertDatabaseHas('payments', [
            'student_fee_id' => $this->studentFee->id,
            'amount_paid' => 1500.00,
        ]);

        $this->assertDatabaseHas('student_fees', ['id' => $this->studentFee->id, 'status' => 'paid']);
    }

    public function test_bursar_can_record_partial_payment(): void
    {
        $this->actingAs($this->bursar);

        $response = $this->post('/bursar/payments', [
            'student_fee_id' => $this->studentFee->id,
            'amount_paid' => '500.00',
            'payment_method' => 'bank_transfer',
            'payment_date' => '2026-07-15',
            'reference' => 'REF-002',
        ]);

        $response->assertRedirect('/bursar/student-fees');
        $this->assertDatabaseHas('student_fees', ['id' => $this->studentFee->id, 'status' => 'partial']);
    }

    public function test_bursar_cannot_overpay(): void
    {
        $this->actingAs($this->bursar);

        $response = $this->post('/bursar/payments', [
            'student_fee_id' => $this->studentFee->id,
            'amount_paid' => '2000.00',
            'payment_method' => 'cash',
            'payment_date' => '2026-07-15',
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('payments', ['student_fee_id' => $this->studentFee->id, 'amount_paid' => 2000.00]);
    }

    public function test_bursar_cannot_pay_future_date(): void
    {
        $this->actingAs($this->bursar);

        $futureDate = now()->addDay()->format('Y-m-d');

        $response = $this->post('/bursar/payments', [
            'student_fee_id' => $this->studentFee->id,
            'amount_paid' => '500.00',
            'payment_method' => 'cash',
            'payment_date' => $futureDate,
        ]);

        $response->assertSessionHasErrors('payment_date');
    }

    public function test_bursar_can_view_payment_receipt(): void
    {
        $this->actingAs($this->bursar);

        // First record a payment
        $payment = Payment::create([
            'student_fee_id' => $this->studentFee->id,
            'receipt_number' => 'RCPT-20260715-ABC123',
            'amount_paid' => 1500.00,
            'payment_method' => 'cash',
            'payment_date' => '2026-07-15',
            'recorded_by' => $this->bursar->id,
        ]);

        $response = $this->get("/bursar/payments/{$payment->id}/receipt");

        $response->assertOk();
        $response->assertViewIs('bursar.receipt');
    }

    public function test_bursar_can_download_payment_receipt_pdf(): void
    {
        $this->actingAs($this->bursar);

        $payment = Payment::create([
            'student_fee_id' => $this->studentFee->id,
            'receipt_number' => 'RCPT-20260715-XYZ789',
            'amount_paid' => 1500.00,
            'payment_method' => 'cash',
            'payment_date' => '2026-07-15',
            'recorded_by' => $this->bursar->id,
        ]);

        $response = $this->get("/bursar/payments/{$payment->id}/receipt?download=1");

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_bursar_can_search_student_fees(): void
    {
        $this->actingAs($this->bursar);

        $response = $this->get('/bursar/student-fees/search-students?q='.$this->student->first_name);

        $response->assertOk();
        $response->assertJsonStructure([0 => ['id', 'label', 'student_name', 'fee_type', 'amount_expected', 'class', 'admission_no']]);
    }

    public function test_bursar_can_view_student_fee_details(): void
    {
        $this->actingAs($this->bursar);

        $response = $this->get("/bursar/student-fees/{$this->studentFee->id}");

        $response->assertOk();
        $response->assertViewIs('bursar.student-fee');
    }

    public function test_bursar_can_access_fee_collections_page(): void
    {
        $this->actingAs($this->bursar);

        $response = $this->get('/bursar/fee-collections');

        $response->assertOk();
        $response->assertViewIs('bursar.fee-collections');
    }

    public function test_admin_cannot_access_bursar_fee_routes(): void
    {
        $this->actingAs($this->admin);

        $routes = [
            '/bursar/student-fees',
            '/bursar/fee-collections',
            '/bursar/payments',
        ];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertForbidden();
        }
    }

    public function test_teacher_cannot_access_bursar_fee_routes(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $this->actingAs($teacher);

        $response = $this->get('/bursar/student-fees');
        $response->assertForbidden();
    }

    public function test_guest_cannot_access_bursar_fee_routes(): void
    {
        $routes = [
            '/bursar/student-fees',
            '/bursar/fee-collections',
            '/bursar/payments',
        ];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertRedirect('/login');
        }
    }

    public function test_bursar_payment_creates_audit_log(): void
    {
        $this->actingAs($this->bursar);

        $this->post('/bursar/payments', [
            'student_fee_id' => $this->studentFee->id,
            'amount_paid' => '1500.00',
            'payment_method' => 'cash',
            'payment_date' => '2026-07-15',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->bursar->id,
            'action' => 'payment.created',
        ]);
    }

    public function test_multiple_partial_payments_sum_correctly(): void
    {
        $this->actingAs($this->bursar);

        // First partial payment
        $this->post('/bursar/payments', [
            'student_fee_id' => $this->studentFee->id,
            'amount_paid' => '500.00',
            'payment_method' => 'cash',
            'payment_date' => '2026-07-15',
        ]);

        // Second partial payment
        $this->post('/bursar/payments', [
            'student_fee_id' => $this->studentFee->id,
            'amount_paid' => '500.00',
            'payment_method' => 'bank_transfer',
            'payment_date' => '2026-07-16',
        ]);

        // Third payment completes it
        $this->post('/bursar/payments', [
            'student_fee_id' => $this->studentFee->id,
            'amount_paid' => '500.00',
            'payment_method' => 'card',
            'payment_date' => '2026-07-17',
        ]);

        $this->assertDatabaseHas('student_fees', ['id' => $this->studentFee->id, 'status' => 'paid']);
        $this->assertEquals(3, $this->studentFee->fresh()->payments->count());
        $this->assertEquals(1500.00, $this->studentFee->fresh()->payments->sum('amount_paid'));
    }

    public function test_bursar_fee_collections_shows_summary_data(): void
    {
        $this->actingAs($this->bursar);

        // Create a payment to have some data
        Payment::create([
            'student_fee_id' => $this->studentFee->id,
            'receipt_number' => 'RCPT-TEST-001',
            'amount_paid' => 1500.00,
            'payment_method' => 'cash',
            'payment_date' => '2026-07-15',
            'recorded_by' => $this->bursar->id,
        ]);

        $response = $this->get('/bursar/fee-collections');

        $response->assertOk();
        $response->assertViewIs('bursar.fee-collections');
    }

    public function test_bursar_can_access_daily_collection_report(): void
    {
        $this->actingAs($this->bursar);

        Payment::create([
            'student_fee_id' => $this->studentFee->id,
            'receipt_number' => 'RCPT-DAILY-001',
            'amount_paid' => 1500.00,
            'payment_method' => 'cash',
            'payment_date' => '2026-07-15',
            'recorded_by' => $this->bursar->id,
        ]);

        $response = $this->get('/bursar/reports/daily?term_id='.$this->term->id);

        $response->assertOk();
    }

    public function test_bursar_can_access_outstanding_debtors_report(): void
    {
        $this->actingAs($this->bursar);

        $response = $this->get('/bursar/reports/debtors');

        $response->assertOk();
    }

    public function test_bursar_can_access_student_statement(): void
    {
        $this->actingAs($this->bursar);

        $response = $this->get("/bursar/reports/student-statement/{$this->student->id}");

        $response->assertOk();
    }
}