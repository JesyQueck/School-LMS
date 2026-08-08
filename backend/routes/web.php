<?php

use App\Http\Controllers\Admin\AcademicStructureController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\ReportCardController;
use App\Http\Controllers\Admin\ResultsController;
use App\Http\Controllers\Admin\SchoolAdminController;
use App\Http\Controllers\Admin\TeacherAssignmentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Parent\AnnouncementsController as ParentAnnouncementsController;
use App\Http\Controllers\Parent\AttendanceController as ParentAttendanceController;
use App\Http\Controllers\Parent\ChildController;
use App\Http\Controllers\Parent\DashboardController as ParentDashboardController;
use App\Http\Controllers\Parent\FeesController as ParentFeesController;
use App\Http\Controllers\Parent\ResultsController as ParentResultsController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\Student\AnnouncementsController as StudentAnnouncementsController;
use App\Http\Controllers\Student\AttendanceController as StudentAttendanceController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\FeesController as StudentFeesController;
use App\Http\Controllers\Student\ReportCardController as StudentReportCardController;
use App\Http\Controllers\Student\ResultsController as StudentResultsController;
use App\Http\Controllers\TeacherPortalController;
use App\Http\Controllers\Teacher\ReportCardController as TeacherReportCardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/about', [PublicController::class, 'about'])->name('about');
Route::get('/contact', [PublicController::class, 'contact'])->name('contact');
Route::get('/admissions', [PublicController::class, 'admissions'])->name('admissions');
Route::get('/announcements', [PublicController::class, 'announcements'])->name('public.announcements');
Route::get('/academics', [PublicController::class, 'academics'])->name('public.academics');
Route::get('/gallery', [PublicController::class, 'gallery'])->name('public.gallery');
Route::get('/news', [PublicController::class, 'news'])->name('public.news');
Route::get('/faq', [PublicController::class, 'faq'])->name('public.faq');
Route::get('/privacy', [PublicController::class, 'privacy'])->name('public.privacy');
Route::get('/terms', [PublicController::class, 'terms'])->name('public.terms');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::middleware(['auth', 'password.only'])->group(function () {
    Route::get('/change-password', [PasswordChangeController::class, 'show'])->name('password.change');
    Route::post('/change-password', [PasswordChangeController::class, 'update'])->name('password.change.update');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->middleware('role:admin');

    Route::middleware('role:teacher')->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', [TeacherPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/class/attendance', [TeacherPortalController::class, 'classAttendance'])->name('class.attendance');
        Route::get('/attendance', [TeacherPortalController::class, 'attendance'])->name('attendance');
        Route::post('/attendance', [TeacherPortalController::class, 'storeAttendance'])->name('attendance.store');
        Route::post('/attendance/start', [TeacherPortalController::class, 'startAttendance'])->name('attendance.start');
        Route::get('/assignments', [TeacherPortalController::class, 'mySubjects'])->name('assignments');
        Route::get('/results', [ResultsController::class, 'teacherResults'])->name('results');
        Route::get('/class-attendance', [TeacherPortalController::class, 'classAttendance'])->name('class-attendance');
        Route::get('/report-cards', [TeacherReportCardController::class, 'index'])->name('report-cards.index');
        Route::post('/report-cards', [TeacherReportCardController::class, 'store'])->name('report-cards.store');
        Route::get('/report-cards/student/{student}', [TeacherReportCardController::class, 'getStudentResults'])->name('report-cards.student.results');
        Route::get('/report-cards/progress', [TeacherReportCardController::class, 'getSubmissionProgress'])->name('report-cards.progress');
        Route::post('/report-cards/{reportCard}/submit', [TeacherReportCardController::class, 'submitForReview'])->name('report-cards.submit');
        Route::get('/report-cards/{reportCard}/download', [TeacherReportCardController::class, 'download'])->name('report-cards.download');
        Route::get('/class-performance', [TeacherReportCardController::class, 'classPerformance'])->name('class-performance');
        Route::get('/parents', [AccountController::class, 'parentCommunication'])->name('parents');
        Route::get('/assessments', [AccountController::class, 'assessments'])->name('assessments');
        Route::get('/profile', [AccountController::class, 'teacherProfile'])->name('profile');
        Route::get('/scores', [\App\Http\Controllers\Teacher\SubjectResultsController::class, 'scores'])->name('scores');
        Route::post('/scores', [\App\Http\Controllers\Teacher\SubjectResultsController::class, 'store'])->name('scores.store');
    });

    Route::middleware('role:parent')->prefix('parent')->name('parent.')->group(function () {
        Route::get('/dashboard', [ParentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/children/{student}', [ChildController::class, 'show'])->name('children.show');
        Route::get('/children/{student}/results', [ParentResultsController::class, 'index'])->name('children.results');
        Route::get('/children/{student}/attendance', [ParentAttendanceController::class, 'index'])->name('children.attendance');
        Route::get('/children/{student}/fees', [ParentFeesController::class, 'index'])->name('children.fees');
        Route::get('/announcements', [ParentAnnouncementsController::class, 'index'])->name('announcements');
    });

    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/results', [StudentResultsController::class, 'index'])->name('results');
        Route::get('/attendance', [StudentAttendanceController::class, 'index'])->name('attendance');
        Route::get('/fees', [StudentFeesController::class, 'index'])->name('fees');
        Route::get('/report-cards', [StudentReportCardController::class, 'index'])->name('report-cards');
        Route::get('/report-cards/{reportCard}/download', [StudentReportCardController::class, 'download'])->name('report-cards.download');
        Route::get('/announcements', [StudentAnnouncementsController::class, 'index'])->name('announcements');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [SchoolAdminController::class, 'index'])->name('index');
        Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
        Route::get('/accounts/create', [AccountController::class, 'create'])->name('accounts.create');
        Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
        Route::get('/classes', [SchoolAdminController::class, 'classes'])->name('classes');
        Route::post('/classes', [SchoolAdminController::class, 'createClass'])->name('classes.store');
        Route::get('/teachers', [SchoolAdminController::class, 'teachers'])->name('teachers');
        Route::post('/teachers', [SchoolAdminController::class, 'createTeacher'])->name('teachers.store');
        Route::get('/students', [SchoolAdminController::class, 'students'])->name('students');
        Route::get('/students/export', [SchoolAdminController::class, 'exportStudents'])->name('students.export');
        Route::post('/students', [SchoolAdminController::class, 'createStudent'])->name('students.store');

        Route::get('/academic', [AcademicStructureController::class, 'index'])->name('academic');
        Route::post('/academic/sessions', [AcademicStructureController::class, 'createSession'])->name('academic.sessions.store');
        Route::post('/academic/terms', [AcademicStructureController::class, 'createTerm'])->name('academic.terms.store');
        Route::post('/academic/subjects', [AcademicStructureController::class, 'createSubject'])->name('academic.subjects.store');
        Route::post('/academic/class-subjects', [AcademicStructureController::class, 'createClassSubject'])->name('academic.class-subjects.store');

        Route::get('/assignments', [TeacherAssignmentController::class, 'index'])->name('assignments');
        Route::post('/assignments', [TeacherAssignmentController::class, 'store'])->name('assignments.store');
        Route::delete('/assignments/{assignment}', [TeacherAssignmentController::class, 'destroy'])->name('assignments.destroy');

        Route::get('/class-assignments', [TeacherAssignmentController::class, 'index'])->name('class-assignments.index');
        Route::post('/class-assignments', [TeacherAssignmentController::class, 'storeClassAssignment'])->name('class-assignments.store');
        Route::get('/class-assignments/{classAssignment}', [TeacherAssignmentController::class, 'showClassAssignment'])->name('class-assignments.show');
        Route::delete('/class-assignments/{classAssignment}', [TeacherAssignmentController::class, 'destroyClassAssignment'])->name('class-assignments.destroy');

        Route::get('/finance', [FinanceController::class, 'index'])->name('finance');
        Route::post('/finance/fee-types', [FinanceController::class, 'createFeeType'])->name('finance.fee-types.store');
        Route::post('/finance/student-fees', [FinanceController::class, 'createStudentFee'])->name('finance.student-fees.store');
        Route::post('/finance/payments', [FinanceController::class, 'createPayment'])->name('finance.payments.store');

        Route::get('/results', [ResultsController::class, 'index'])->name('results');
        Route::get('/results/export', [ResultsController::class, 'exportResults'])->name('results.export');
        Route::post('/results', [ResultsController::class, 'store'])->name('results.store');
        Route::post('/results/{result}/lock', [ResultsController::class, 'lock'])->name('results.lock');

        Route::get('/report-cards', [ReportCardController::class, 'index'])->name('report-cards');
        Route::post('/report-cards', [ReportCardController::class, 'store'])->name('report-cards.store');
        Route::post('/report-cards/{reportCard}/publish', [ReportCardController::class, 'publish'])->name('report-cards.publish');
        Route::post('/report-cards/{reportCard}/approve', [ReportCardController::class, 'approve'])->name('report-cards.approve');
        Route::post('/report-cards/{reportCard}/return', [ReportCardController::class, 'returnForCorrection'])->name('report-cards.return');
        Route::post('/report-cards/publish-all/{term}', [ReportCardController::class, 'publishAll'])->name('report-cards.publish-all');
        Route::get('/report-cards/{reportCard}/download', [ReportCardController::class, 'download'])->name('report-cards.download');
    });
});