# Claude Code Prompt — School Management System
## Nigerian Secondary School | Single Deployment | Laravel Stack

---

## PROJECT CONTEXT

You are helping me build a full-stack School Management System for a single Nigerian secondary school. This is NOT a SaaS product. It is one deployment for one school.

The system has two parts:
1. A **public-facing website** (school's online presence)
2. A **multi-role portal** (internal management system)

---

## TECH STACK

- **Backend:** Laravel (PHP) — latest stable version
- **Database:** MySQL
- **Frontend:** Laravel Blade + Tailwind CSS
- **PDF Generation:** barryvdh/laravel-dompdf
- **Excel Import/Export:** maatwebsite/excel
- **Authentication:** Laravel Breeze (customised)
- **Hosting Target:** cPanel shared hosting (Whogohost)

---

## USER ROLES

There are exactly 4 roles. All users share one `users` table with a `role` column:

| Role | Access |
|---|---|
| `admin` | Full system access |
| `teacher` | Attendance, results for assigned subjects, own timetable |
| `parent` | Children's results (after publish), attendance, fees, announcements |
| `student` | Own results (after publish), timetable, attendance, announcements |

Role-based access must be enforced at the **route level using middleware**, not just at the UI level.

---

## DATABASE SCHEMA

Create migrations for the following tables in this exact order (respects foreign key dependencies):

### 1. users
```
id, name, email, password, role (enum: admin/teacher/parent/student),
phone, profile_photo, is_active (boolean, default true),
remember_token, timestamps
```

### 2. sessions (academic sessions, NOT Laravel sessions)
```
id, name (e.g. 2024/2025), start_date, end_date,
is_current (boolean, only one true at a time), timestamps
```

### 3. terms
```
id, session_id (FK), name (enum: first/second/third),
start_date, end_date, is_current (boolean), timestamps
```

### 4. classes
```
id, name (e.g. JSS1A, SS2B), form_teacher_id (nullable FK → teachers),
timestamps
```

### 5. subjects
```
id, name, class_id (FK → classes), timestamps
```

### 6. teachers
```
id, user_id (FK → users), employee_id, qualification, timestamps
```

### 7. teacher_subject (pivot)
```
teacher_id (FK → teachers), subject_id (FK → subjects)
```

### 8. students
```
id, user_id (FK → users), admission_no (unique), class_id (FK → classes),
house, gender, state_of_origin, date_of_birth, blood_group,
emergency_contact, emergency_phone, status (enum: active/graduated/withdrawn/suspended),
timestamps
```

### 9. parents
```
id, user_id (FK → users), occupation, timestamps
```

### 10. parent_student (pivot)
```
parent_id (FK → parents), student_id (FK → students)
```

### 11. fee_types
```
id, name (e.g. Tuition, PTA Levy, Exam Fee), amount,
term_id (FK → terms), class_id (nullable FK, for class-specific fees), timestamps
```

### 12. fees
```
id, student_id (FK), fee_type_id (FK), term_id (FK),
amount_expected, amount_paid, status (enum: paid/partial/unpaid),
payment_date, receipt_number, recorded_by (FK → users), timestamps
```

### 13. results
```
id, student_id (FK), subject_id (FK), term_id (FK),
ca_score (decimal), exam_score (decimal), total (computed: ca + exam),
grade (varchar), remark (varchar), submitted_by (FK → users),
is_locked (boolean, default false), timestamps
```

### 14. attendance
```
id, student_id (FK), class_id (FK), term_id (FK), date,
status (enum: present/absent/late), marked_by (FK → users), timestamps
```

### 15. timetable
```
id, class_id (FK), subject_id (FK), teacher_id (FK),
day (enum: Monday/Tuesday/Wednesday/Thursday/Friday),
start_time, end_time, timestamps
```

### 16. announcements
```
id, title, body (text), created_by (FK → users),
target_role (enum: all/teacher/parent/student), timestamps
```

### 17. report_cards
```
id, student_id (FK), term_id (FK), class_teacher_remark (text),
principal_remark (text), position_in_class (integer),
total_students_in_class (integer), next_term_begins (date),
is_published (boolean, default false), generated_at, timestamps
```

### 18. audit_logs
```
id, user_id (FK), action (varchar), target_model (varchar),
target_id (integer), old_value (json nullable),
new_value (json nullable), ip_address, timestamps
```

---

## RBAC IMPLEMENTATION

### Middleware
Create `app/Http/Middleware/RoleMiddleware.php`:
```php
public function handle($request, Closure $next, $role)
{
    if (!auth()->check() || auth()->user()->role !== $role) {
        abort(403, 'Unauthorized');
    }
    return $next($request);
}
```

Register it in `bootstrap/app.php` (Laravel 11) or `Kernel.php` (Laravel 10) as `role`.

### Route Groups in web.php
```php
// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // all admin routes here
});

// Teacher routes
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    // all teacher routes here
});

// Parent routes
Route::middleware(['auth', 'role:parent'])->prefix('parent')->name('parent.')->group(function () {
    // all parent routes here
});

// Student routes
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    // all student routes here
});
```

### Login Redirect by Role
After login, redirect based on role:
```php
match(auth()->user()->role) {
    'admin'   => redirect()->route('admin.dashboard'),
    'teacher' => redirect()->route('teacher.dashboard'),
    'parent'  => redirect()->route('parent.dashboard'),
    'student' => redirect()->route('student.dashboard'),
};
```

---

## FOLDER STRUCTURE

```
app/Http/Controllers/
├── Admin/
│   ├── DashboardController.php
│   ├── StudentController.php
│   ├── TeacherController.php
│   ├── ClassController.php
│   ├── SubjectController.php
│   ├── SessionController.php
│   ├── FeeController.php
│   ├── ResultController.php
│   ├── AttendanceController.php
│   ├── TimetableController.php
│   ├── AnnouncementController.php
│   └── ReportCardController.php
├── Teacher/
│   ├── DashboardController.php
│   ├── AttendanceController.php
│   └── ResultController.php
├── Parent/
│   └── DashboardController.php
├── Student/
│   └── DashboardController.php
└── Auth/
    └── LoginController.php

app/Http/Middleware/
└── RoleMiddleware.php

app/Models/
├── User.php
├── Student.php
├── Teacher.php
├── ParentProfile.php  (avoid naming conflict with PHP's 'Parent')
├── Classes.php        (or SchoolClass.php to avoid PHP keyword)
├── Subject.php
├── AcademicSession.php
├── Term.php
├── Fee.php
├── FeeType.php
├── Result.php
├── Attendance.php
├── Timetable.php
├── Announcement.php
├── ReportCard.php
└── AuditLog.php

app/Services/
├── ResultService.php      (grade calculation logic)
├── FeeService.php         (payment recording)
├── ReportCardService.php  (PDF generation)
└── AttendanceService.php  (summary calculations)

resources/views/
├── layouts/
│   ├── public.blade.php
│   ├── admin.blade.php
│   ├── teacher.blade.php
│   ├── parent.blade.php
│   └── student.blade.php
├── public/
├── admin/
│   ├── dashboard.blade.php
│   ├── students/
│   ├── teachers/
│   ├── classes/
│   ├── fees/
│   ├── results/
│   ├── attendance/
│   ├── timetable/
│   └── announcements/
├── teacher/
├── parent/
└── student/
```

---

## GRADING LOGIC

Put this in `ResultService.php`. Do NOT hardcode it in controllers:

```php
public function calculateGrade(float $total): array
{
    return match(true) {
        $total >= 75 => ['grade' => 'A1', 'remark' => 'Excellent'],
        $total >= 70 => ['grade' => 'B2', 'remark' => 'Very Good'],
        $total >= 65 => ['grade' => 'B3', 'remark' => 'Good'],
        $total >= 60 => ['grade' => 'C4', 'remark' => 'Credit'],
        $total >= 55 => ['grade' => 'C5', 'remark' => 'Credit'],
        $total >= 50 => ['grade' => 'C6', 'remark' => 'Credit'],
        $total >= 45 => ['grade' => 'D7', 'remark' => 'Pass'],
        $total >= 40 => ['grade' => 'E8', 'remark' => 'Pass'],
        default      => ['grade' => 'F9', 'remark' => 'Fail'],
    };
}
```

---

## RESULT PUBLISH CONTROL

Results have two gates:
1. **Lock** — admin locks a term so teachers can no longer edit results
2. **Publish** — admin publishes results so parents/students can view them

In the parent and student controllers, always scope result queries:
```php
// Only show results from published report cards
Result::where('student_id', $student->id)
      ->where('term_id', $currentTerm->id)
      ->whereHas('term.reportCards', fn($q) => $q->where('is_published', true))
      ->get();
```

---

## AUDIT LOG

Create a helper method or trait that logs sensitive actions. Call it in controllers whenever:
- A result is created or edited
- A fee payment is recorded
- A student record is created, edited, or deactivated
- A report card is published

```php
AuditLog::create([
    'user_id'      => auth()->id(),
    'action'       => 'updated result',
    'target_model' => 'Result',
    'target_id'    => $result->id,
    'old_value'    => $oldValues,   // json
    'new_value'    => $newValues,   // json
    'ip_address'   => request()->ip(),
]);
```

---

## DEMO SEEDER DATA

Seed the following for demo/pitch purposes:
- 1 admin account
- 3 teachers (Mathematics, English, Biology)
- 3 classes (JSS1A, JSS2B, SS3A)
- 15 students distributed across the 3 classes
- 3 parents each linked to 2 students
- Results for First and Second term
- Fee records (mix of paid, partial, unpaid)
- A timetable for JSS2B
- 2 announcements (one for all, one for parents only)
- 1 academic session (2024/2025) marked as current
- Current term set to Third

---

## WHAT NOT TO BUILD

Do not build any of the following unless I explicitly ask:
- Multi-tenancy or school switching
- Subscription or billing system
- Online payment gateway
- Mobile app or PWA
- Student admission application form
- Library or hostel management
- In-app messaging or chat

---

## BUILD ORDER

Follow this sequence strictly. Do not move to the next phase until the current one is complete and tested:

1. Laravel project setup, all migrations, all models with relationships
2. Authentication — login, logout, password reset, first-login password change
3. RBAC middleware, route groups, login redirect by role
4. Admin module — students, classes, teachers (CRUD)
5. Admin module — sessions, terms, fee types, fee payments
6. Admin module — result entry, grade calculation, lock/publish controls
7. PDF report card generation
8. Teacher module — attendance marking, result submission
9. Parent module — child dashboard, results, fees, announcements
10. Student module — results, timetable, attendance, announcements
11. Timetable builder and announcement system
12. Excel bulk import (students) and exports (results, fees, student list)
13. Audit log implementation
14. Public website (homepage, about, programs, gallery, contact)
15. Demo data seeder
16. Deployment configuration for cPanel

---

## NOTES FOR CODE GENERATION

- Use `SchoolClass` or `Classes` as the model name to avoid PHP reserved word `Class`
- Use `ParentProfile` as the model name to avoid PHP reserved word `Parent`
- Always use Form Request classes for validation, never validate inside controllers directly
- All business logic goes in Service classes, not controllers
- Controllers should only: receive request → call service → return view/response
- Use Eloquent relationships everywhere, avoid raw SQL queries
- Every Blade layout must include CSRF token meta tag
- Tailwind CSS via CDN is acceptable for demo; switch to compiled for production
