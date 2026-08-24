<?php

/**
 * Cleanup Script: Clear all student records and parent profiles.
 *
 * This removes students, parent profiles, and any user records
 * that are exclusively for students and parents (no cross-references).
 * Also clears student-related data (contacts, documents, emergency contacts).
 * Run with: php scripts/clear_students.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ImportCredential;
use App\Models\ParentProfile;
use App\Models\Student;
use App\Models\StudentContact;
use App\Models\StudentDocument;
use App\Models\StudentEmergencyContact;
use App\Models\User;
use Illuminate\Support\Facades\DB;

DB::transaction(function () {
    $studentUserIds = Student::where('user_id', '!=', null)->pluck('user_id')->toArray();
    $parentUserIds = ParentProfile::where('user_id', '!=', null)->pluck('user_id')->toArray();
    $allUserIds = array_unique(array_merge($studentUserIds, $parentUserIds));

    StudentContact::whereIn('student_id', Student::pluck('id'))->delete();
    StudentEmergencyContact::whereIn('student_id', Student::pluck('id'))->delete();
    StudentDocument::whereIn('student_id', Student::pluck('id'))->delete();

    $studentCount = Student::count();
    $parentCount = ParentProfile::count();

    Student::query()->delete();
    ParentProfile::query()->delete();

    User::whereIn('id', $allUserIds)->whereIn('role', ['student', 'parent'])->delete();

    ImportCredential::whereIn('role', ['student', 'parent'])->delete();

    echo "Deleted {$studentCount} student(s) and {$parentCount} parent(s).\n";
    echo "Cleaned up associated user records, contacts, documents, and credentials.\n";
    echo "Done. You can now re-import students via the admin UI.\n";
});
