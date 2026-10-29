<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$source = 'school_lms_recovery';
$target = config('database.connections.mysql.database');

if ($target !== 'school_lms') {
    throw new RuntimeException("STOP: Unexpected target database: {$target}");
}

config([
    'database.connections.recovery' => array_merge(
        config('database.connections.mysql'),
        ['database' => $source]
    ),
]);

DB::purge('mysql');
DB::purge('recovery');

$src = DB::connection('recovery');
$dst = DB::connection('mysql');
$srcSchema = Schema::connection('recovery');
$dstSchema = Schema::connection('mysql');

$tables = [
    'academic_sessions',
    'users',
    'subjects',
    'terms',
    'teachers',
    'classes',
    'students',
    'parents',
    'class_subjects',
    'class_assignments',
    'parent_student',
    'teacher_class_subjects',
    'student_contacts',
    'student_documents',
    'student_emergency_contacts',
    'attendances',
    'fee_types',
    'student_fees',
    'payments',
    'results',
    'report_cards',
    'announcements',
    'audit_logs',
    'import_credentials',
    'period_configs',
    'periods',
    'timetables',
];

$report = [];

try {
    $dst->transaction(function () use (
        $tables, $src, $dst, $srcSchema, $dstSchema, $source, &$report
    ) {
        foreach ($tables as $table) {
            if (
                !$srcSchema->hasTable($table) ||
                !$dstSchema->hasTable($table)
            ) {
                throw new RuntimeException("Missing source or target table: {$table}");
            }

            $sourceColumns = $srcSchema->getColumnListing($table);
            $targetColumns = $dstSchema->getColumnListing($table);
            $columns = array_values(array_intersect($sourceColumns, $targetColumns));

            $primary = $src->select(
                "SELECT COLUMN_NAME
                 FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = ?
                   AND TABLE_NAME = ?
                   AND CONSTRAINT_NAME = 'PRIMARY'
                 ORDER BY ORDINAL_POSITION",
                [$source, $table]
            );

            $keys = array_map(fn ($row) => $row->COLUMN_NAME, $primary);

            if (!$keys || array_diff($keys, $columns)) {
                throw new RuntimeException("Cannot identify compatible primary key: {$table}");
            }

            $inserted = 0;
            $skipped = 0;
            $sourceRows = $src->table($table)->get();

            foreach ($sourceRows as $row) {
                $data = [];

                foreach ($columns as $column) {
                    $data[$column] = $row->$column;
                }

                $query = $dst->table($table);

                foreach ($keys as $key) {
                    $query->where($key, '=', $row->$key);
                }

                if ($query->exists()) {
                    $skipped++;
                    continue;
                }

                $dst->table($table)->insert($data);
                $inserted++;
            }

            $report[] = [
                'table' => $table,
                'source' => count($sourceRows),
                'inserted' => $inserted,
                'skipped' => $skipped,
            ];

            echo "{$table}: source=" . count($sourceRows)
                . ", inserted={$inserted}, skipped={$skipped}\n";
        }
    });

    echo "\nSUCCESS: Recovery transaction committed.\n";
} catch (\Throwable $e) {
    echo "\nFAILED: Recovery transaction rolled back.\n";
    echo $e->getMessage() . "\n";
    exit(1);
}

