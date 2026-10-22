<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\AuditLog;
use App\Models\ClassSubject;
use App\Models\FeeType;
use App\Models\Payment;
use App\Models\ReportCard;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Services\AuditLogFormatter;
use App\Traits\AuditsActions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);
    }

    public function test_admin_can_create_class_and_audit_log_is_recorded(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($adminUser);

        $response = $this->post('/admin/classes', [
            'name' => 'JSS 2',
        ]);

        $response->assertRedirect('/admin/classes');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $adminUser->id,
            'action' => 'class.created',
            'target_model' => SchoolClass::class,
        ]);
    }

    public function test_admin_can_create_student_and_audit_log_is_recorded(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'admin',
        ]);

        $class = SchoolClass::create([
            'name' => 'JSS 1',
        ]);

        $this->actingAs($adminUser);

        $response = $this->post('/admin/students', [
            'name' => 'Jane Student',
            'first_name' => 'Jane',
            'last_name' => 'Student',
            'email' => 'jane.student@example.com',
            'parent_email' => 'parent.jane@example.com',
            'parent_name' => 'Parent Name',
            'parent_phone' => '08012345678',
            'admission_no' => 'GRA-0001',
            'class_id' => $class->id,
            'date_of_birth' => '2012-05-10',
            'gender' => 'female',
            'password' => 'Password123!',
            'emergency_1_name' => 'Emergency Contact',
            'emergency_1_relationship' => 'Uncle',
            'emergency_1_phone' => '08098765432',
        ]);

        $response->assertRedirect('/admin/students');

        $student = Student::whereHas('user', function ($query) {
            $query->where('email', 'jane.student@example.com');
        })->first();

        $this->assertNotNull($student);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $adminUser->id,
            'action' => 'student.created',
            'target_model' => Student::class,
            'target_id' => $student->id,
        ]);
    }

    public function test_admin_can_publish_report_card_and_audit_log_is_recorded(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'admin',
        ]);

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

        $class = SchoolClass::create([
            'name' => 'JSS 1',
        ]);

        $student = Student::create([
            'user_id' => User::factory()->create()->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM704',
        ]);

        $subject = Subject::create(['name' => 'Mathematics']);
        $classSubject = ClassSubject::create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
        ]);

        Result::create([
            'student_id' => $student->id,
            'class_subject_id' => $classSubject->id,
            'term_id' => $term->id,
            'ca_score' => 30,
            'exam_score' => 50,
            'total' => 80,
            'grade' => 'A1',
            'is_locked' => true,
        ]);

        $reportCard = ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $term->id,
            'class_id' => $class->id,
            'status' => ReportCard::STATUS_DRAFT,
        ]);

        $this->actingAs($adminUser);

        $response = $this->post(
            "/admin/report-cards/{$reportCard->id}/publish"
        );

        $response->assertRedirect('/admin/report-cards');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $adminUser->id,
            'action' => 'report_card.published',
            'target_model' => ReportCard::class,
            'target_id' => $reportCard->id,
        ]);
    }

    public function test_audit_log_stores_user_agent_from_http_request(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);

        $this->actingAs($adminUser);

        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';

        $response = $this->withHeaders([
            'User-Agent' => $userAgent,
        ])->post('/admin/classes', [
            'name' => 'JSS 3',
        ]);

        $response->assertRedirect('/admin/classes');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'class.created',
            'user_agent' => $userAgent,
        ]);
    }

    public function test_audit_log_user_agent_matches_the_request_that_generated_it(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);

        $this->actingAs($adminUser);

        $class = SchoolClass::create(['name' => 'JSS 1']);

        $userAgent1 = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)';
        $userAgent2 = 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)';

        $this->withHeaders(['User-Agent' => $userAgent1])->post('/admin/students', [
            'name' => 'Student One',
            'first_name' => 'Student',
            'last_name' => 'One',
            'email' => 'one@example.com',
            'parent_email' => 'parent.one@example.com',
            'parent_name' => 'Parent Name',
            'parent_phone' => '08012345678',
            'admission_no' => 'GRA-0001',
            'class_id' => $class->id,
            'date_of_birth' => '2012-01-01',
            'gender' => 'male',
            'emergency_1_name' => 'Emergency Contact',
            'emergency_1_relationship' => 'Uncle',
            'emergency_1_phone' => '08098765432',
            'password' => 'Password123!',
        ]);

        $this->withHeaders(['User-Agent' => $userAgent2])->post('/admin/students', [
            'name' => 'Student Two',
            'first_name' => 'Student',
            'last_name' => 'Two',
            'email' => 'two@example.com',
            'parent_email' => 'parent.two@example.com',
            'parent_name' => 'Parent Name',
            'parent_phone' => '08012345678',
            'admission_no' => 'GRA-0002',
            'class_id' => $class->id,
            'date_of_birth' => '2012-02-02',
            'gender' => 'female',
            'emergency_1_name' => 'Emergency Contact',
            'emergency_1_relationship' => 'Aunt',
            'emergency_1_phone' => '08098765433',
            'password' => 'Password123!',
        ]);

        $log1 = AuditLog::where('action', 'student.created')
            ->where('user_agent', $userAgent1)
            ->exists();
        $log2 = AuditLog::where('action', 'student.created')
            ->where('user_agent', $userAgent2)
            ->exists();

        $this->assertTrue($log1, 'Audit log should contain user agent from first request');
        $this->assertTrue($log2, 'Audit log should contain user agent from second request');
        $this->assertNotEquals($userAgent1, $userAgent2);
    }

    public function test_audit_log_still_records_ip_address(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);

        $this->actingAs($adminUser);

        $response = $this->withHeaders([
            'REMOTE_ADDR' => '192.168.1.100',
            'User-Agent' => 'TestAgent/1.0',
        ])->post('/admin/classes', [
            'name' => 'JSS 4',
        ]);

        $response->assertRedirect('/admin/classes');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'class.created',
            'ip_address' => '192.168.1.100',
            'user_agent' => 'TestAgent/1.0',
        ]);
    }

    public function test_audit_logging_does_not_fail_without_http_request(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);

        $request = Request::create('/', 'GET');
        $request->headers->remove('User-Agent');
        $request->headers->remove('X-Forwarded-For');
        $request->headers->remove('Client-Ip');
        $request->server->remove('REMOTE_ADDR');

        $auditor = new class
        {
            use AuditsActions;

            public function invokeAudit(Request $request, string $action, ?string $targetModel = null, ?int $targetId = null, ?array $oldValue = null, ?array $newValue = null): void
            {
                $this->audit($request, $action, $targetModel, $targetId, $oldValue, $newValue);
            }
        };

        $auditor->invokeAudit($request, 'test.action', User::class, $adminUser->id, null, null);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'test.action',
            'target_model' => User::class,
            'target_id' => $adminUser->id,
            'user_agent' => null,
        ]);
    }

    public function test_admin_audit_log_page_shows_friendly_actions_actors_targets_and_keeps_filters(): void
    {
        $admin = User::factory()->create(['name' => 'Principal Debowale', 'role' => 'admin']);
        $session = AcademicSession::firstOrFail();
        $term = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
        ]);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        $studentUser = User::factory()->create(['role' => 'student']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'first_name' => 'Amina',
            'last_name' => 'Bello',
            'admission_no' => 'GAA/2026/001',
        ]);
        $feeType = FeeType::create([
            'name' => 'First Term Tuition',
            'amount' => 1000,
            'term_id' => $term->id,
        ]);
        $studentFee = StudentFee::create([
            'student_id' => $student->id,
            'fee_type_id' => $feeType->id,
            'term_id' => $term->id,
            'amount_expected' => 1000,
        ]);
        $payment = Payment::create([
            'student_fee_id' => $studentFee->id,
            'receipt_number' => 'RCPT-AUDIT-1',
            'amount_paid' => 500,
            'payment_date' => '2026-10-22',
            'recorded_by' => $admin->id,
        ]);
        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'payment.created',
            'target_model' => Payment::class,
            'target_id' => $payment->id,
            'ip_address' => '127.0.0.1',
        ]);
        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'user.login',
            'target_model' => User::class,
            'target_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.audit-logs.index', ['action' => 'payment.created']));

        $response->assertOk()
            ->assertSee('Payment recorded')
            ->assertSee('payment.created')
            ->assertSee('Principal Debowale')
            ->assertSee('Payment RCPT-AUDIT-1 · Amina Bello')
            ->assertSee('127.0.0.1')
            ->assertDontSee('Logged in')
            ->assertSee('payment.created');
    }

    public function test_audit_formatter_maps_confirmed_actions_and_humanizes_unknown_codes(): void
    {
        $formatter = app(AuditLogFormatter::class);
        $actions = [
            'user.login' => 'Logged in',
            'user.logout' => 'Logged out',
            'payment.created' => 'Payment recorded',
            'result.locked' => 'Result locked',
            'fee_type.created' => 'Fee type created',
        ];
        $logs = collect($actions)->values()->map(fn ($action, $index) => tap(new AuditLog([
            'action' => array_keys($actions)[$index],
        ]), fn (AuditLog $log) => $log->id = $index + 1));
        $formatted = $formatter->format($logs);

        $this->assertSame(array_values($actions), array_column($formatted, 'action'));

        $unknown = new AuditLog(['action' => 'legacy.event_code']);
        $unknown->id = 999;
        $unknown->user_id = 999;
        $formattedUnknown = $formatter->format([$unknown]);

        $this->assertSame('Legacy Event Code', $formattedUnknown[999]['action']);
        $this->assertSame('Unknown user', $formattedUnknown[999]['actor']);
    }

    public function test_audit_formatter_handles_login_targets_and_deleted_target_records(): void
    {
        $formatter = app(AuditLogFormatter::class);
        $admin = User::factory()->create(['role' => 'admin']);
        $login = AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'user.login',
            'target_model' => User::class,
            'target_id' => $admin->id,
        ]);
        $class = SchoolClass::create(['name' => 'Deleted class']);
        $deletedTarget = AuditLog::create([
            'action' => 'class.deleted',
            'target_model' => SchoolClass::class,
            'target_id' => $class->id,
        ]);
        $class->delete();

        $formatted = $formatter->format([$login, $deletedTarget]);

        $this->assertSame('Admin account', $formatted[$login->id]['target']);
        $this->assertSame('Record no longer available', $formatted[$deletedTarget->id]['target']);
    }

    public function test_deleted_audit_actor_is_shown_as_unknown_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $log = AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'class.created',
            'target_model' => SchoolClass::class,
            'target_id' => SchoolClass::create(['name' => 'JSS 1'])->id,
        ]);
        $admin->delete();

        $formatted = app(AuditLogFormatter::class)->format([$log->fresh()]);

        $this->assertSame('Unknown user', $formatted[$log->id]['actor']);
    }

    public function test_non_admin_cannot_view_audit_logs(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)
            ->get(route('admin.audit-logs.index'))
            ->assertForbidden();
    }
}
