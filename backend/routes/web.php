<?php

use App\Http\Controllers\Admin\AcademicStructureController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\SchoolAdminController;
use App\Http\Controllers\Admin\TeacherAssignmentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->middleware('role:admin');
    Route::get('/teacher/dashboard', [DashboardController::class, 'teacher'])->middleware('role:teacher');
    Route::get('/parent/dashboard', [DashboardController::class, 'parent'])->middleware('role:parent');
    Route::get('/student/dashboard', [DashboardController::class, 'student'])->middleware('role:student');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [SchoolAdminController::class, 'index'])->name('index');
        Route::get('/classes', [SchoolAdminController::class, 'classes'])->name('classes');
        Route::post('/classes', [SchoolAdminController::class, 'createClass'])->name('classes.store');
        Route::get('/teachers', [SchoolAdminController::class, 'teachers'])->name('teachers');
        Route::post('/teachers', [SchoolAdminController::class, 'createTeacher'])->name('teachers.store');
        Route::get('/students', [SchoolAdminController::class, 'students'])->name('students');
        Route::post('/students', [SchoolAdminController::class, 'createStudent'])->name('students.store');

        Route::get('/academic', [AcademicStructureController::class, 'index'])->name('academic');
        Route::post('/academic/sessions', [AcademicStructureController::class, 'createSession'])->name('academic.sessions.store');
        Route::post('/academic/terms', [AcademicStructureController::class, 'createTerm'])->name('academic.terms.store');
        Route::post('/academic/subjects', [AcademicStructureController::class, 'createSubject'])->name('academic.subjects.store');
        Route::post('/academic/class-subjects', [AcademicStructureController::class, 'createClassSubject'])->name('academic.class-subjects.store');

        Route::get('/assignments', [TeacherAssignmentController::class, 'index'])->name('assignments');
        Route::post('/assignments', [TeacherAssignmentController::class, 'store'])->name('assignments.store');

        Route::get('/finance', [FinanceController::class, 'index'])->name('finance');
        Route::post('/finance/fee-types', [FinanceController::class, 'createFeeType'])->name('finance.fee-types.store');
        Route::post('/finance/student-fees', [FinanceController::class, 'createStudentFee'])->name('finance.student-fees.store');
        Route::post('/finance/payments', [FinanceController::class, 'createPayment'])->name('finance.payments.store');
    });
});
