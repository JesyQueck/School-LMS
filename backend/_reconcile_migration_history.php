<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$historical = [
    '0001_01_01_000000_create_users_table',
    '0001_01_01_000001_create_cache_table',
    '0001_01_01_000002_create_jobs_table',
    '2026_07_30_000001_create_users_and_auth_tables',
    '2026_07_30_000002_create_academic_structure_tables',
    '2026_07_30_000003_create_staff_and_student_tables',
    '2026_07_30_000004_create_fee_and_result_tables',
    '2026_07_30_000005_create_attendance_timetable_announcement_and_report_tables',
    '2026_07_31_000001_add_publish_columns_to_report_cards',
    '2026_07_31_132727_add_name_columns_to_students_table',
    '2026_08_06_000000_add_needs_password_change_to_users_table',
    '2026_08_06_000001_create_class_assignments_table',
    '2026_08_07_000000_add_status_to_report_cards',
    '2026_08_07_000001_add_assessment_fields_to_report_cards',
    '2026_08_12_101318_make_term_dates_nullable',
    '2026_08_14_000000_add_class_id_to_report_cards',
    '2026_08_14_102040_rename_singular_tables_to_plural',
    '2026_08_14_131816_add_user_agent_to_audit_logs_table',
    '2026_08_14_133852_add_reference_and_notes_to_payments_table',
    '2026_08_14_140547_drop_is_published_from_report_cards_table',
    '2026_08_14_140704_add_unique_constraint_to_fee_types_table',
    '2026_08_18_000001_add_enrollment_fields_to_students_table',
    '2026_08_18_000002_add_fields_to_parents_table',
    '2026_08_18_000003_create_student_contacts_table',
    '2026_08_18_000004_create_student_emergency_contacts_table',
    '2026_08_18_000005_create_student_documents_table',
    '2026_08_20_000001_create_import_credentials_table',
    '2026_08_21_000001_add_term_and_session_to_timetables',
    '2026_08_21_000002_create_period_configs_and_update_timetables',
    '2026_08_25_070000_add_description_to_fee_types_table',
    '2026_08_25_080000_add_show_on_website_to_announcements_table',
    '2026_08_27_000001_add_event_fields_to_announcements_table',
    '2026_08_27_000001_add_image_to_announcements_table',
    '2026_08_28_143748_add_ca_max_and_exam_max_to_class_subjects_table',
    '2026_10_21_000001_allow_role_login_without_student_email',
    '2026_10_21_000002_add_encrypted_temporary_password_to_import_credentials',
    '2026_10_22_000001_add_is_synthetic_to_attendances_table',
    '2026_10_24_000001_add_day_rules_to_period_configs',
    '2026_10_24_000002_add_period_name_override_to_timetables',
    '2026_10_27_000001_add_schedules_and_period_type',
    '2026_10_30_000000_add_review_fields_to_report_cards',
    '2026_11_04_083236_create_fee_packages_and_components_tables',
    '2026_11_04_083255_create_fee_package_assignments_table',
    '2026_11_04_083307_create_student_fee_component_snapshots_table',
    '2026_11_04_083316_create_payment_reversals_table',
    '2026_11_04_090401_add_fee_source_to_student_fees_table',
];

$payroll = [
    '2026_11_07_135000_create_employees_table',
    '2026_11_07_135001_create_salary_profiles_table',
    '2026_11_07_135002_create_salary_components_table',
    '2026_11_07_135003_create_salary_profile_components_table',
    '2026_11_07_135004_create_payroll_periods_table',
    '2026_11_07_135005_create_payroll_records_table',
    '2026_11_07_135006_create_payroll_record_components_table',
    '2026_11_07_135007_create_payslips_table',
    '2026_11_07_135008_add_payroll_integrity_constraints',
];

$existing = '2026_11_05_203907_add_staff_id_to_users_table';

function stopReconciliation(string $message): never
{
    fwrite(STDERR, "STOP: {$message}\n");
    exit(1);
}

if (DB::connection()->getDatabaseName() !== 'school_lms') {
    stopReconciliation('Connected database is not school_lms.');
}

if (count($historical) !== 46 || count($payroll) !== 9) {
    stopReconciliation('Expected migration list counts do not match.');
}

$diskFiles = array_map(
    fn ($file) => basename($file, '.php'),
    glob(__DIR__ . '/database/migrations/*.php')
);

$expectedFiles = array_merge($historical, $payroll, [$existing]);

sort($diskFiles);
sort($expectedFiles);

if ($diskFiles !== $expectedFiles) {
    stopReconciliation('Migration files on disk do not exactly match the approved 56-file inventory.');
}

$current = DB::table('migrations')->orderBy('id')->get();

if ($current->count() !== 1 ||
    $current[0]->migration !== $existing ||
    (int) $current[0]->batch !== 1) {
    stopReconciliation('Migration history changed. Expected only the existing batch-1 record.');
}

DB::transaction(function () use ($historical, $payroll, $existing) {
    $rows = array_map(
        fn ($name) => ['migration' => $name, 'batch' => 1],
        $historical
    );

    $inserted = DB::table('migrations')->insertOrIgnore($rows);

    if ($inserted !== 46) {
        throw new RuntimeException("Expected 46 inserted rows; database reported {$inserted}.");
    }

    $total = DB::table('migrations')->count();

    if ($total !== 47) {
        throw new RuntimeException("Expected 47 total migration records; found {$total}.");
    }

    $missingHistorical = DB::table('migrations')
        ->whereIn('migration', $historical)
        ->count();

    if ($missingHistorical !== 46) {
        throw new RuntimeException('Not all 46 historical migration records were verified.');
    }

    $payrollPresent = DB::table('migrations')
        ->whereIn('migration', $payroll)
        ->count();

    if ($payrollPresent !== 0) {
        throw new RuntimeException('One or more payroll migrations were unexpectedly recorded.');
    }

    $duplicateNames = DB::table('migrations')
        ->select('migration')
        ->groupBy('migration')
        ->havingRaw('COUNT(*) > 1')
        ->count();

    if ($duplicateNames !== 0) {
        throw new RuntimeException('Duplicate migration names detected.');
    }

    $wrongBatch = DB::table('migrations')
        ->where('batch', '<>', 1)
        ->count();

    if ($wrongBatch !== 0) {
        throw new RuntimeException('Unexpected batch assignment detected.');
    }
});

echo "RECONCILIATION SUCCESSFUL\n";
echo "Database: " . DB::connection()->getDatabaseName() . "\n";
echo "Historical records inserted: 46\n";
echo "Existing record preserved: {$existing}\n";
echo "Total migration records: " . DB::table('migrations')->count() . "\n";
echo "Payroll migration records: " . DB::table('migrations')->whereIn('migration', $payroll)->count() . "\n";
echo "Duplicate migration names: 0\n";
echo "Batch assignment: 1\n";