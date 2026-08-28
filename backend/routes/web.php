<?php

use App\Http\Controllers\Admin\AcademicStructureController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AnnouncementsController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\ReportCardController;
use App\Http\Controllers\Admin\ResultsController;
use App\Http\Controllers\Admin\SchoolAdminController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherAssignmentController;
use App\Http\Controllers\Admin\TimetableController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Parent\AnnouncementsController as ParentAnnouncementsController;
use App\Http\Controllers\Parent\AttendanceController as ParentAttendanceController;
use App\Http\Controllers\Parent\ChildController;
use App\Http\Controllers\Parent\DashboardController as ParentDashboardController;
use App\Http\Controllers\Parent\FeesController as ParentFeesController;
use App\Http\Controllers\Parent\ParentReportCardController;
use App\Http\Controllers\Parent\ParentTimetableController;
use App\Http\Controllers\Parent\ResultsController as ParentResultsController;
use App\Http\Controllers\ProfilePhotoController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\Student\AnnouncementsController as StudentAnnouncementsController;
use App\Http\Controllers\Student\AttendanceController as StudentAttendanceController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\FeesController as StudentFeesController;
use App\Http\Controllers\Student\ProfileController as StudentProfileController;
use App\Http\Controllers\Student\ReportCardController as StudentReportCardController;
use App\Http\Controllers\Student\TimetableController as StudentTimetableController;
use App\Http\Controllers\Teacher\ReportCardController as TeacherReportCardController;
use App\Http\Controllers\Teacher\SubjectResultsController;
use App\Http\Controllers\TeacherPortalController;
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
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login');
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

Route::middleware(['auth', 'password.only'])->group(function () {
    Route::get('/settings', [SettingController::class, 'profile'])->name('settings.profile');
    Route::patch('/settings', [SettingController::class, 'updateProfile'])->name('settings.profile.update');

    Route::post('/profile/photo', [ProfilePhotoController::class, 'update'])->name('profile.photo.update');
    Route::delete('/profile/photo', [ProfilePhotoController::class, 'destroy'])->name('profile.photo.destroy');

    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard')->middleware('role:admin');

    Route::middleware('role:teacher')->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', [TeacherPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/classes', [TeacherPortalController::class, 'classes'])->name('classes.index');
        Route::get('/classes/{class}', [TeacherPortalController::class, 'classStudents'])->name('classes.show');
        Route::get('/my-students', [TeacherPortalController::class, 'myStudents'])->name('students.index');

        Route::get('/class/attendance', [TeacherPortalController::class, 'classAttendance'])->name('class.attendance');
        Route::get('/attendance', [TeacherPortalController::class, 'attendance'])->name('attendance');
        Route::post('/attendance', [TeacherPortalController::class, 'storeAttendance'])->name('attendance.store');
        Route::post('/attendance/start', [TeacherPortalController::class, 'startAttendance'])->name('attendance.start');
        Route::get('/assignments', [TeacherPortalController::class, 'mySubjects'])->name('assignments');
        Route::get('/results', [TeacherPortalController::class, 'results'])->name('results');
        Route::get('/class-attendance', [TeacherPortalController::class, 'classAttendance'])->name('class-attendance');
        Route::get('/report-cards', [TeacherReportCardController::class, 'index'])->name('report-cards.index');
        Route::post('/report-cards', [TeacherReportCardController::class, 'store'])->name('report-cards.store');
        Route::get('/report-cards/student/{student}', [TeacherReportCardController::class, 'getStudentResults'])->name('report-cards.student.results');
        Route::get('/report-cards/progress', [TeacherReportCardController::class, 'getSubmissionProgress'])->name('report-cards.progress');
        Route::post('/report-cards/{reportCard}/submit', [TeacherReportCardController::class, 'submitForReview'])->name('report-cards.submit');
        Route::get('/report-cards/{reportCard}/download', [TeacherReportCardController::class, 'download'])->name('report-cards.download');
        Route::get('/class-performance', [TeacherReportCardController::class, 'classPerformance'])->name('class-performance');
        Route::get('/timetable', [TeacherPortalController::class, 'timetable'])->name('timetable');
        Route::get('/profile', [TeacherPortalController::class, 'profile'])->name('profile');
        Route::get('/announcements', [TeacherPortalController::class, 'announcements'])->name('announcements');
        Route::get('/parents', [AccountController::class, 'parentCommunication'])->name('parents');
        Route::get('/assessments', [AccountController::class, 'assessments'])->name('assessments');
        Route::get('/scores', [SubjectResultsController::class, 'scores'])->name('scores');
        Route::post('/scores', [SubjectResultsController::class, 'store'])->name('scores.store');
    });

    Route::middleware(['auth', 'password.only', 'role:teacher,admin'])->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/students/{student}', [TeacherPortalController::class, 'studentProfile'])->name('students.show');
    });

    Route::middleware('role:parent')->prefix('parent')->name('parent.')->group(function () {
        Route::get('/dashboard', [ParentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/children/{student}', [ChildController::class, 'show'])->name('children.show');
        Route::get('/children/{student}/results', [ParentResultsController::class, 'index'])->name('children.results');
        Route::get('/children/{student}/attendance', [ParentAttendanceController::class, 'index'])->name('children.attendance');
        Route::get('/children/{student}/fees', [ParentFeesController::class, 'index'])->name('children.fees');
        Route::get('/children/{student}/report-cards', [ParentReportCardController::class, 'index'])->name('children.report-cards');
        Route::get('/children/{student}/report-cards/{reportCard}/download', [ParentReportCardController::class, 'download'])->name('children.report-cards.download');
        Route::get('/timetable', [ParentTimetableController::class, 'index'])->name('timetable');
        Route::get('/announcements', [ParentAnnouncementsController::class, 'index'])->name('announcements');
    });

    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/attendance', [StudentAttendanceController::class, 'index'])->name('attendance');
        Route::get('/fees', [StudentFeesController::class, 'index'])->name('fees');
        Route::get('/report-cards', [StudentReportCardController::class, 'index'])->name('report-cards');
        Route::get('/report-cards/{reportCard}/preview', [StudentReportCardController::class, 'preview'])->name('report-cards.preview');
        Route::get('/report-cards/{reportCard}/render', [StudentReportCardController::class, 'render'])->name('report-cards.render');
        Route::get('/report-cards/{reportCard}/download', [StudentReportCardController::class, 'download'])->name('report-cards.download');
        Route::get('/timetable', [StudentTimetableController::class, 'index'])->name('timetable');
        Route::get('/profile', [StudentProfileController::class, 'index'])->name('profile');
        Route::get('/announcements', [StudentAnnouncementsController::class, 'index'])->name('announcements');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', fn () => redirect()->route('admin.dashboard'))->name('index');
        Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
        Route::get('/accounts/create', [AccountController::class, 'create'])->name('accounts.create');
        Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
        Route::get('/accounts/credentials', [AccountController::class, 'credentials'])->name('accounts.credentials');
        Route::get('/accounts/credentials/download', [AccountController::class, 'downloadAllCredentials'])->name('accounts.credentials.download');
        Route::get('/classes', [SchoolAdminController::class, 'classes'])->name('classes');
        Route::post('/classes', [SchoolAdminController::class, 'createClass'])->name('classes.store');
        Route::get('/classes/{class}/edit', [SchoolAdminController::class, 'editClass'])->name('classes.edit');
        Route::put('/classes/{class}', [SchoolAdminController::class, 'updateClass'])->name('classes.update');
        Route::get('/teachers', [SchoolAdminController::class, 'teachers'])->name('teachers');
        Route::post('/teachers', [SchoolAdminController::class, 'createTeacher'])->name('teachers.store');
        Route::get('/teachers/{teacher}/edit', [SchoolAdminController::class, 'editTeacher'])->name('teachers.edit');
        Route::put('/teachers/{teacher}', [SchoolAdminController::class, 'updateTeacher'])->name('teachers.update');
        Route::delete('/teachers/{teacher}', [SchoolAdminController::class, 'destroyTeacher'])->name('teachers.destroy');
        Route::get('/students', [SchoolAdminController::class, 'students'])->name('students');
        Route::get('/students/enroll', [SchoolAdminController::class, 'enrollStudent'])->name('students.enroll');
        Route::get('/students/export', [SchoolAdminController::class, 'exportStudents'])->name('students.export');
        Route::post('/students', [SchoolAdminController::class, 'createStudent'])->name('students.store');
        Route::get('/students/{student}/edit', [SchoolAdminController::class, 'editStudent'])->name('students.edit');
        Route::put('/students/{student}', [SchoolAdminController::class, 'updateStudent'])->name('students.update');

        Route::post('/students/{student}/photo', [ProfilePhotoController::class, 'updateForStudent'])->name('students.photo.update');
        Route::delete('/students/{student}/photo', [ProfilePhotoController::class, 'destroyForStudent'])->name('students.photo.destroy');

        Route::get('/students/import', [SchoolAdminController::class, 'showImportForm'])->name('students.import');
        Route::get('/students/import/template', [SchoolAdminController::class, 'downloadTemplate'])->name('students.import.template');
        Route::post('/students/import/preview', [SchoolAdminController::class, 'importPreview'])->name('students.import.preview');
        Route::post('/students/import/confirm', [SchoolAdminController::class, 'importConfirm'])->name('students.import.confirm');
        Route::post('/students/import/cancel', [SchoolAdminController::class, 'cancelImport'])->name('students.import.cancel');
        Route::get('/students/import/errors/download', [SchoolAdminController::class, 'downloadImportErrors'])->name('students.import.errors');
        Route::get('/students/import/credentials/download', [SchoolAdminController::class, 'downloadImportCredentials'])->name('students.import.credentials');
        Route::get('/students/import/credentials', [SchoolAdminController::class, 'viewImportCredentials'])->name('students.import.credentials.view');
        Route::post('/students/import/credentials/mark-viewed', [SchoolAdminController::class, 'markCredentialsViewed'])->name('students.import.credentials.mark-viewed');

        Route::get('/academic', [AcademicStructureController::class, 'index'])->name('academic');
        Route::post('/academic/sessions', [AcademicStructureController::class, 'createSession'])->name('academic.sessions.store');
        Route::put('/academic/sessions/{session}', [AcademicStructureController::class, 'updateSession'])->name('academic.sessions.update');
        Route::post('/academic/terms', [AcademicStructureController::class, 'createTerm'])->name('academic.terms.store');
        Route::post('/academic/class-subjects', [AcademicStructureController::class, 'createClassSubject'])->name('academic.class_subjects.store');
        Route::put('/academic/class-subjects/{classSubject}', [AcademicStructureController::class, 'updateClassSubject'])->name('academic.class_subjects.update');
        Route::delete('/academic/class-subjects/{classSubject}', [AcademicStructureController::class, 'destroyClassSubject'])->name('academic.class_subjects.destroy');
        Route::post('/academic/sessions/{session}/current', [AcademicStructureController::class, 'makeSessionCurrent'])->name('academic.sessions.current');
        Route::post('/academic/terms/{term}/current', [AcademicStructureController::class, 'makeTermCurrent'])->name('academic.terms.current');
        Route::put('/academic/terms/{term}', [AcademicStructureController::class, 'updateTerm'])->name('academic.terms.update');

        Route::get('/assignments', [TeacherAssignmentController::class, 'index'])->name('assignments');
        Route::post('/assignments', [TeacherAssignmentController::class, 'store'])->name('assignments.store');
        Route::delete('/assignments/{assignment}', [TeacherAssignmentController::class, 'destroy'])->name('assignments.destroy');

        Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
        Route::get('/subjects/create', [SubjectController::class, 'create'])->name('subjects.create');
        Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
        Route::get('/subjects/{subject}/edit', [SubjectController::class, 'edit'])->name('subjects.edit');
        Route::put('/subjects/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
        Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');

        Route::get('/class-assignments', [TeacherAssignmentController::class, 'index'])->name('class-assignments.index');
        Route::post('/class-assignments', [TeacherAssignmentController::class, 'storeClassAssignment'])->name('class-assignments.store');
        Route::get('/class-assignments/{classAssignment}', [TeacherAssignmentController::class, 'showClassAssignment'])->name('class-assignments.show');
        Route::delete('/class-assignments/{classAssignment}', [TeacherAssignmentController::class, 'destroyClassAssignment'])->name('class-assignments.destroy');

        Route::get('/finance', [FinanceController::class, 'index'])->name('finance');
        Route::post('/finance/fee-types', [FinanceController::class, 'createFeeType'])->name('finance.fee-types.store');
        Route::post('/finance/payments', [FinanceController::class, 'createStudentFeePayment'])->name('finance.payments.store');
        Route::get('/finance/student-fees/{studentFee}', [FinanceController::class, 'showStudentFee'])->name('finance.student-fees.show');
        Route::get('/finance/payments/{payment}/receipt', [FinanceController::class, 'paymentReceipt'])->name('finance.payments.receipt');
        Route::get('/finance/search-students', [FinanceController::class, 'searchStudentFees'])->name('finance.search-students');
        Route::get('/finance/report/export', [FinanceController::class, 'exportFinancialReport'])->name('finance.report.export');

        Route::get('/announcements', [AnnouncementsController::class, 'index'])->name('announcements');
        Route::get('/announcements/create', [AnnouncementsController::class, 'create'])->name('announcements.create');
        Route::post('/announcements', [AnnouncementsController::class, 'store'])->name('announcements.store');
        Route::get('/announcements/{announcement}/edit', [AnnouncementsController::class, 'edit'])->name('announcements.edit');
        Route::patch('/announcements/{announcement}', [AnnouncementsController::class, 'update'])->name('announcements.update');
        Route::delete('/announcements/{announcement}', [AnnouncementsController::class, 'destroy'])->name('announcements.destroy');

        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');

        Route::get('/results', [ResultsController::class, 'index'])->name('results');
        Route::post('/results', [ResultsController::class, 'store'])->name('results.store');
        Route::post('/results/{result}/lock', [ResultsController::class, 'lock'])->name('results.lock');
        Route::get('/results/export', [ResultsController::class, 'exportResults'])->name('results.export');

        Route::get('/report-cards', [ReportCardController::class, 'index'])->name('report-cards.index');
        Route::post('/report-cards', [ReportCardController::class, 'store'])->name('report-cards.store');
        Route::post('/report-cards/{reportCard}/publish', [ReportCardController::class, 'publish'])->name('report-cards.publish');
        Route::post('/report-cards/{reportCard}/unpublish', [ReportCardController::class, 'unpublish'])->name('report-cards.unpublish');
        Route::post('/report-cards/{reportCard}/approve', [ReportCardController::class, 'approve'])->name('report-cards.approve');
        Route::post('/report-cards/{reportCard}/return', [ReportCardController::class, 'returnForCorrection'])->name('report-cards.return');
        Route::post('/report-cards/publish-all/{term}', [ReportCardController::class, 'publishAll'])->name('report-cards.publish-all');
        Route::get('/report-cards/{reportCard}/download', [ReportCardController::class, 'download'])->name('report-cards.download');

        Route::get('/timetable', [TimetableController::class, 'index'])->name('timetable.index');
        Route::get('/timetable/create', [TimetableController::class, 'create'])->name('timetable.create');
        Route::post('/timetable', [TimetableController::class, 'store'])->name('timetable.store');
        Route::get('/timetable/{timetable}/edit', [TimetableController::class, 'edit'])->name('timetable.edit');
        Route::put('/timetable/{timetable}', [TimetableController::class, 'update'])->name('timetable.update');
        Route::delete('/timetable/{timetable}', [TimetableController::class, 'destroy'])->name('timetable.destroy');
        Route::post('/timetable/periods', [TimetableController::class, 'savePeriodConfig'])->name('timetable.periods.store');
        Route::post('/timetable/generate', [TimetableController::class, 'generate'])->name('timetable.generate');
        Route::get('/timetable/preview', [TimetableController::class, 'preview'])->name('timetable.preview');
        Route::post('/timetable/confirm-generate', [TimetableController::class, 'confirmGenerate'])->name('timetable.confirm-generate');
        Route::post('/timetable/{timetable}/move', [TimetableController::class, 'move'])->name('timetable.move');
        Route::post('/timetable/swap', [TimetableController::class, 'swap'])->name('timetable.swap');
    });
});
