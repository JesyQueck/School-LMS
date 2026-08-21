<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportStudentsRequest;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Mail\EnrollmentNotification;
use App\Models\AcademicSession;
use App\Models\ImportCredential;
use App\Models\ParentProfile;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentContact;
use App\Models\StudentDocument;
use App\Models\StudentEmergencyContact;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\CsvExportService;
use App\Services\StudentImportService;
use App\Traits\AuditsActions;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv as CsvWriter;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

class SchoolAdminController extends Controller
{
    use AuditsActions;

    public function __construct(
        protected CsvExportService $csvExportService,
    ) {}

    public function index()
    {
        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $totalClasses = SchoolClass::count();
        $totalParents = ParentProfile::count();
        $activeClasses = SchoolClass::count();
        $subjects = Subject::count();
        $teachersAssigned = Teacher::whereHas('classAssignments')->count();
        $totalTeachersForAssignment = Teacher::count();
        $session = AcademicSession::where('is_current', true)->value('name') ?? 'N/A';
        $termName = 'N/A';
        $resultsSubmitted = 0;
        $resultsLocked = 0;
        $resultsPending = 0;
        $finance = ['expected' => 0, 'collected' => 0, 'outstanding' => 0, 'collection_rate' => 0, 'paid' => 0, 'partial' => 0, 'unpaid' => 0];
        $recentActivity = AuditLog::latest()->take(5)->get();

        return view('admin.index', compact(
            'totalStudents',
            'totalTeachers',
            'totalClasses',
            'totalParents',
            'activeClasses',
            'subjects',
            'teachersAssigned',
            'totalTeachersForAssignment',
            'session',
            'termName',
            'resultsSubmitted',
            'resultsLocked',
            'resultsPending',
            'finance',
            'recentActivity',
        ));
    }

    public function classes()
    {
        return view('admin.classes.index', [
            'classes' => SchoolClass::with('formTeacher.user')->get(),
            'teachers' => Teacher::with('user')->get(),
        ]);
    }

    public function teachers()
    {
        return view('admin.teachers.index', [
            'teachers' => Teacher::with('user')->get(),
            'users' => User::where('role', 'teacher')->where('needs_password_change', true)->get(),
        ]);
    }

    public function students()
    {
        return view('admin.students.index', [
            'students' => Student::with(['user', 'schoolClass'])->get(),
            'classes' => SchoolClass::all(),
            'users' => User::where('role', 'student')->where('needs_password_change', true)->get(),
        ]);
    }

    public function enrollStudent()
    {
        return view('admin.students.enroll', [
            'classes' => SchoolClass::orderBy('name')->get(),
            'academicSessions' => AcademicSession::orderBy('start_date', 'desc')->get(),
        ]);
    }

    public function createClass(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'form_teacher_id' => ['nullable', 'exists:teachers,id'],
        ]);

        if (empty($data['name'])) {
            return back()->withErrors(['name' => 'Class name is required.']);
        }

        SchoolClass::create($data);

        $this->audit($request, 'class.created', SchoolClass::class, SchoolClass::query()->latest('id')->value('id'), null, $data);

        return redirect()->route('admin.classes')->with('status', 'Class created.');
    }

    public function editClass(SchoolClass $class)
    {
        return view('admin.classes.edit', [
            'class' => $class->load('formTeacher.user'),
            'teachers' => Teacher::with('user')->get(),
        ]);
    }

    public function updateClass(Request $request, SchoolClass $class)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:classes,name,'.$class->id],
            'form_teacher_id' => ['nullable', 'exists:teachers,id'],
        ]);

        $oldData = $class->getOriginal();

        $class->update($data);

        $this->audit($request, 'class.updated', SchoolClass::class, $class->id, $oldData, $data);

        return redirect()->route('admin.classes')->with('status', 'Class updated.');
    }

    public function createTeacher(StoreTeacherRequest $request)
    {
        $data = $request->validated();

        $temporaryPassword = $data['password'] ?? Str::random(12);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($temporaryPassword),
            'phone' => $data['phone'] ?? null,
            'role' => 'teacher',
            'is_active' => true,
            'needs_password_change' => true,
        ]);

        Teacher::create([
            'user_id' => $user->id,
            'qualification' => $data['qualification'],
            'phone' => $data['phone'] ?? null,
            'employee_id' => 'T-'.Str::upper(Str::random(6)),
        ]);

        $this->persistImportCredentials(collect([
            [
                'role' => 'teacher',
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $temporaryPassword,
                'related_to' => null,
                'user_id' => $user->id,
            ],
        ]), auth()->id());

        try {
            Mail::to($user->email)->send(new EnrollmentNotification($user, $temporaryPassword));
        } catch (\Exception $e) {
        }

        $this->audit($request, 'teacher.created', Teacher::class, Teacher::query()->latest('id')->value('id'), null, [
            'user_id' => $user->id,
            'temp_password' => $temporaryPassword,
        ]);

        return redirect()->route('admin.teachers')->with('status', "Teacher created. Temporary password: {$temporaryPassword}")
            ->with('new_teacher_credentials', [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $temporaryPassword,
            ]);
    }

    public function editTeacher(Teacher $teacher)
    {
        return view('admin.teachers.edit', [
            'teacher' => $teacher->load('user'),
        ]);
    }

    public function updateTeacher(UpdateTeacherRequest $request, Teacher $teacher)
    {
        $data = $request->validated();

        $userUpdates = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ];

        if (! empty($data['password'])) {
            $userUpdates['password'] = Hash::make($data['password']);
        }

        $teacher->user()->update($userUpdates);

        $teacher->update([
            'qualification' => $data['qualification'] ?? null,
        ]);

        $this->audit($request, 'teacher.updated', Teacher::class, $teacher->id, null, $data);

        return redirect()->route('admin.teachers')->with('status', 'Teacher updated.');
    }

    public function destroyTeacher(Request $request, Teacher $teacher)
    {
        if (SchoolClass::where('form_teacher_id', $teacher->id)->exists()) {
            return redirect()->route('admin.teachers')->withErrors(['teacher' => 'Cannot delete a teacher assigned as a form teacher to a class.']);
        }

        $teacherName = $teacher->user->name ?? 'Unknown';
        $this->audit($request, 'teacher.deleted', Teacher::class, $teacher->id);
        $teacher->user()->delete();
        $teacher->delete();

        return redirect()->route('admin.teachers')->with('status', "Teacher {$teacherName} deleted.");
    }

    public function createStudent(StoreStudentRequest $request)
    {
        $data = $request->validated();

        $temporaryPassword = $data['password'] ?? Str::random(12);

        $parent = $this->getOrCreateParent($data['parent_email'], $data);

        $studentEmail = $data['email'] ?? null ?: Str::slug($data['name'], '.').'.'.Str::random(6).'@placeholder.local';

        $user = User::create([
            'name' => $data['name'],
            'email' => $studentEmail,
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($temporaryPassword),
            'role' => 'student',
            'is_active' => true,
            'needs_password_change' => true,
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'admission_no' => $data['admission_no'],
            'class_id' => $data['class_id'],
            'admission_date' => $data['admission_date'],
            'academic_session_id' => $data['academic_session_id'],
            'student_type' => $data['student_type'],
            'previous_school' => $data['previous_school'] ?? null,
            'previous_school_address' => $data['previous_school_address'] ?? null,
            'previous_class' => $data['previous_class'] ?? null,
            'previous_year_attended' => $data['previous_year_attended'] ?? null,
            'house' => $data['house'] ?? null,
            'gender' => $data['gender'],
            'state_of_origin' => $data['state_of_origin'] ?? null,
            'nationality' => $data['nationality'] ?? null,
            'lga' => $data['lga'] ?? null,
            'religion' => $data['religion'] ?? null,
            'date_of_birth' => $data['date_of_birth'],
            'blood_group' => $data['blood_group'] ?? null,
            'home_address' => $data['home_address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'emergency_contact' => $data['emergency_contact'] ?? null,
            'emergency_phone' => $data['emergency_phone'] ?? null,
            'status' => 'active',
        ]);

        if ($parent) {
            $student->parents()->attach($parent->id);
        }

        $this->persistImportCredentials(collect([
            [
                'role' => 'student',
                'name' => $data['name'],
                'email' => $studentEmail,
                'password' => $temporaryPassword,
                'related_to' => $parent ? ($parent->user->name ?? '') : null,
                'user_id' => $user->id,
            ],
        ]), auth()->id());

        try {
            Mail::to($user->email)->send(new EnrollmentNotification(
                $user,
                $temporaryPassword,
                $parent ? ($parent->user->name ?? '') : '',
            ));
        } catch (\Exception $e) {
        }

        if ($parent) {
            try {
                Mail::to($parent->user->email)->send(new EnrollmentNotification(
                    $parent->user,
                    $temporaryPassword,
                    $data['name'],
                ));
            } catch (\Exception $e) {
            }
        }

        $this->createStudentContacts($student, $data);
        $this->createEmergencyContacts($student, $data);
        $this->handleDocumentUploads($student, $data, $request);

        $this->audit($request, 'student.created', Student::class, $student->id, null, [
            'user_id' => $user->id,
            'admission_no' => $data['admission_no'],
            'temp_password' => $temporaryPassword,
        ]);

        $redirectMessage = 'Student enrolled. Admission No: '.$data['admission_no'];
        if ($parent) {
            $redirectMessage .= ', Parent linked';
        }
        $redirectMessage .= '. Temporary password: '.$temporaryPassword;

        return redirect()->route('admin.students')->with('status', $redirectMessage);
    }

    protected function createStudentContacts(Student $student, array $data): void
    {
        $contacts = [];

        if (! empty($data['father_name'])) {
            $contacts[] = [
                'student_id' => $student->id,
                'type' => 'father',
                'full_name' => $data['father_name'],
                'phone' => $data['father_phone'] ?? null,
                'whatsapp' => $data['father_whatsapp'] ?? null,
                'email' => $data['father_email'] ?? null,
                'occupation' => $data['father_occupation'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (! empty($data['mother_name'])) {
            $contacts[] = [
                'student_id' => $student->id,
                'type' => 'mother',
                'full_name' => $data['mother_name'],
                'phone' => $data['mother_phone'] ?? null,
                'whatsapp' => $data['mother_whatsapp'] ?? null,
                'email' => $data['mother_email'] ?? null,
                'occupation' => $data['mother_occupation'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($contacts) {
            StudentContact::insert($contacts);
        }
    }

    protected function createEmergencyContacts(Student $student, array $data): void
    {
        $contacts = [];

        $contacts[] = [
            'student_id' => $student->id,
            'name' => $data['emergency_1_name'],
            'relationship' => $data['emergency_1_relationship'],
            'phone' => $data['emergency_1_phone'],
            'whatsapp' => $data['emergency_1_whatsapp'] ?? null,
            'address' => $data['emergency_1_address'] ?? null,
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (! empty($data['emergency_2_name'])) {
            $contacts[] = [
                'student_id' => $student->id,
                'name' => $data['emergency_2_name'],
                'relationship' => $data['emergency_2_relationship'],
                'phone' => $data['emergency_2_phone'] ?? null,
                'whatsapp' => $data['emergency_2_whatsapp'] ?? null,
                'address' => $data['emergency_2_address'] ?? null,
                'is_primary' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        StudentEmergencyContact::insert($contacts);
    }

    protected function handleDocumentUploads(Student $student, array $data, Request $request): void
    {
        $documentFields = [
            'document_passport' => 'passport',
            'document_birth_certificate' => 'birth_certificate',
            'document_previous_result' => 'previous_result',
            'document_transfer_certificate' => 'transfer_certificate',
            'document_identification' => 'identification',
            'document_other' => 'other',
        ];

        foreach ($documentFields as $field => $type) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $fileName = Str::uuid().'.'.$file->getClientOriginalExtension();
                $filePath = $file->storeAs('students/'.$student->id.'/documents', $fileName, 'public');

                StudentDocument::create([
                    'student_id' => $student->id,
                    'type' => $type,
                    'file_path' => $filePath,
                    'file_name' => $file->getClientOriginalName(),
                    'uploaded_at' => now(),
                ]);
            }
        }
    }

    public function editStudent(Student $student)
    {
        return view('admin.students.edit', [
            'student' => $student->load('user', 'schoolClass', 'parents.user'),
            'classes' => SchoolClass::orderBy('name')->get(),
            'academicSessions' => AcademicSession::orderBy('start_date', 'desc')->get(),
        ]);
    }

    public function updateStudent(UpdateStudentRequest $request, Student $student)
    {
        $data = $request->validated();

        $student->user()->update([
            'name' => $data['name'],
            'email' => $data['email'] ?? ($student->user->email ?? null),
            'phone' => $data['phone'] ?? null,
        ]);

        if (! empty($data['password'])) {
            $student->user()->update(['password' => Hash::make($data['password'])]);
        }

        $student->update([
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'admission_no' => $data['admission_no'],
            'class_id' => $data['class_id'],
            'admission_date' => $data['admission_date'],
            'academic_session_id' => $data['academic_session_id'],
            'student_type' => $data['student_type'],
            'previous_school' => $data['previous_school'] ?? null,
            'previous_school_address' => $data['previous_school_address'] ?? null,
            'previous_class' => $data['previous_class'] ?? null,
            'previous_year_attended' => $data['previous_year_attended'] ?? null,
            'house' => $data['house'] ?? null,
            'gender' => $data['gender'],
            'state_of_origin' => $data['state_of_origin'] ?? null,
            'nationality' => $data['nationality'] ?? null,
            'lga' => $data['lga'] ?? null,
            'religion' => $data['religion'] ?? null,
            'date_of_birth' => $data['date_of_birth'],
            'blood_group' => $data['blood_group'] ?? null,
            'home_address' => $data['home_address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'emergency_contact' => $data['emergency_contact'] ?? null,
            'emergency_phone' => $data['emergency_phone'] ?? null,
        ]);

        $this->audit($request, 'student.updated', Student::class, $student->id, null, $data);

        return redirect()->route('admin.students')->with('status', 'Student updated.');
    }

    protected function getOrCreateParent(string $parentEmail, array $requestData)
    {
        $existingParent = ParentProfile::whereHas('user', fn ($q) => $q->where('email', $parentEmail))->first();

        if ($existingParent) {
            $existingParent->user()->update([
                'phone' => $requestData['parent_phone'] ?? $existingParent->user->phone,
            ]);

            $existingParent->update([
                'occupation' => $requestData['parent_occupation'] ?? $existingParent->occupation,
                'phone' => $requestData['parent_phone'] ?? $existingParent->phone,
                'first_name' => $requestData['parent_first_name'] ?? $existingParent->first_name,
                'last_name' => $requestData['parent_last_name'] ?? $existingParent->last_name,
                'relationship_to_student' => $requestData['parent_relationship'] ?? $existingParent->relationship_to_student,
                'whatsapp' => $requestData['parent_whatsapp'] ?? $existingParent->whatsapp,
                'address' => $requestData['parent_address'] ?? $existingParent->address,
                'city' => $requestData['parent_city'] ?? $existingParent->city,
                'state' => $requestData['parent_state'] ?? $existingParent->state,
            ]);

            return $existingParent;
        }

        $parentUser = User::create([
            'name' => $requestData['parent_name'] ?? 'Parent of '.$requestData['name'],
            'email' => $parentEmail,
            'password' => Hash::make(Str::random(12)),
            'phone' => $requestData['parent_phone'] ?? null,
            'role' => 'parent',
            'is_active' => true,
            'needs_password_change' => true,
        ]);

        return ParentProfile::create([
            'user_id' => $parentUser->id,
            'occupation' => $requestData['parent_occupation'] ?? null,
            'phone' => $requestData['parent_phone'] ?? null,
            'first_name' => $requestData['parent_first_name'] ?? null,
            'last_name' => $requestData['parent_last_name'] ?? null,
            'relationship_to_student' => $requestData['parent_relationship'] ?? null,
            'whatsapp' => $requestData['parent_whatsapp'] ?? null,
            'address' => $requestData['parent_address'] ?? null,
            'city' => $requestData['parent_city'] ?? null,
            'state' => $requestData['parent_state'] ?? null,
        ]);
    }

    public function exportStudents(Request $request)
    {
        return $this->csvExportService->exportStudents($request);
    }

    public function showImportForm()
    {
        $importCredentials = ImportCredential::where('created_by', auth()->id())
            ->whereNull('viewed_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest('created_at')
            ->get();

        $pendingCredentialsCount = $importCredentials->count();

        return view('admin.students.import', [
            'pendingCredentialsCount' => $pendingCredentialsCount,
        ]);
    }

    public function downloadTemplate(Request $request)
    {
        $format = $request->query('format', 'csv');

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $headers = StudentImportService::REQUIRED_COLUMNS;
        $sheet->fromArray($headers, null, 'A1');

        $exampleRow = [
            'GAA/2024/001',
            'Amina',
            'Bello',
            '2012-05-14',
            'Female',
            '2026-09-01',
            '2026/2027',
            'JSS 1',
            'New',
            'Ahmed Bello',
            'Father',
            '08030000001',
        ];
        $sheet->fromArray($exampleRow, null, 'A2');

        if ($format === 'xlsx') {
            $response = Response::stream(function () use ($spreadsheet) {
                $writer = new XlsxWriter($spreadsheet);
                $writer->save('php://output');
            }, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="student-import-template.xlsx"',
            ]);
        } else {
            $response = Response::stream(function () use ($spreadsheet) {
                $writer = new CsvWriter($spreadsheet);
                $writer->save('php://output');
            }, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="student-import-template.csv"',
            ]);
        }

        return $response;
    }

    public function importPreview(ImportStudentsRequest $request, StudentImportService $importService)
    {
        $file = $request->file('import_file');
        $tempPath = $file->getRealPath();

        $result = $importService->preview($tempPath);

        session()->forget('import_stats');

        session([
            'student_import_preview' => [
                'temp_path' => $tempPath,
                'valid_rows' => $result['valid_rows'],
                'invalid_rows' => $result['invalid_rows'],
                'total_rows' => $result['total_rows'],
                'warnings' => $result['warnings'],
            ],
        ]);

        return redirect()->route('admin.students.import')->with('preview', true);
    }

    public function importConfirm(Request $request, StudentImportService $importService)
    {
        $previewData = session('student_import_preview');

        if (! $previewData) {
            return redirect()->route('admin.students.import')->with('error', 'No import data found. Please upload a file first.');
        }

        $validRows = collect($previewData['valid_rows']);

        if ($validRows->isEmpty()) {
            $this->cleanupTempFile($previewData['temp_path'] ?? null);

            return redirect()->route('admin.students.import')->with('error', 'No valid rows to import.');
        }

        $stats = $importService->import($validRows);

        if ($stats['imported'] > 0) {
            $this->persistImportCredentials($stats['credentials'], auth()->id());
        }

        $this->cleanupTempFile($previewData['temp_path'] ?? null);
        session()->forget('import_stats');
        session()->forget('student_import_preview');

        return redirect()->route('admin.students.import')->with('status', sprintf(
            'Import complete. %d students imported, %d parents created, %d parents reused. %d errors. %d login credentials generated.',
            $stats['imported'],
            $stats['parents_created'],
            $stats['parents_reused'],
            $stats['errors']->count(),
            $stats['credentials']->count(),
        ));
    }

    public function viewImportCredentials(Request $request)
    {
        return redirect()->route('admin.accounts.credentials');
    }

    public function markCredentialsViewed(Request $request)
    {
        return redirect()->route('admin.accounts.credentials');
    }

    public function downloadImportErrors(Request $request)
    {
        $previewData = session('student_import_preview');

        if (! $previewData) {
            return redirect()->route('admin.students.import')->with('error', 'No errors to download.');
        }

        $invalidRows = collect($previewData['invalid_rows'] ?? []);

        if ($invalidRows->isEmpty()) {
            return redirect()->route('admin.students.import')->with('error', 'No errors to download.');
        }

        $response = Response::stream(function () use ($invalidRows) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['row_number', 'field', 'value', 'error']);

            foreach ($invalidRows as $invalidRow) {
                $errors = collect($invalidRow['errors'] ?? []);
                foreach ($errors as $error) {
                    fputcsv($output, [
                        $invalidRow['row_number'],
                        $error['field'],
                        $error['value'] ?? '',
                        $error['error'],
                    ]);
                }
            }

            fclose($output);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="import-errors.csv"',
        ]);

        return $response;
    }

    public function cancelImport(Request $request)
    {
        $previewData = session('student_import_preview');
        $this->cleanupTempFile($previewData['temp_path'] ?? null);
        session()->forget('student_import_preview');
        session()->forget('import_stats');

        return redirect()->route('admin.students.import')->with('status', 'Import cancelled.');
    }

    protected function persistImportCredentials(Collection $credentials, int $createdBy): void
    {
        foreach ($credentials as $credential) {
            ImportCredential::create([
                'role' => $credential['role'],
                'name' => $credential['name'],
                'email' => $credential['email'],
                'password' => $credential['password'],
                'related_to' => $credential['related_to'] ?? null,
                'user_id' => $credential['user_id'] ?? null,
                'created_by' => $createdBy,
                'expires_at' => null,
            ]);
        }
    }

    public function downloadImportCredentials(Request $request)
    {
        $credentials = ImportCredential::where('created_by', auth()->id())
            ->whereNull('viewed_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest('created_at')
            ->get();

        if ($credentials->isEmpty()) {
            return redirect()->route('admin.students')->with('error', 'No credentials to download.');
        }

        $response = Response::stream(function () use ($credentials) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Role', 'Name', 'Email', 'Temporary Password', 'Related Student']);

            foreach ($credentials as $credential) {
                fputcsv($output, [
                    $credential->role,
                    $credential->name,
                    $credential->email,
                    $credential->password,
                    $credential->related_to ?? '',
                ]);
            }

            fclose($output);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="import-credentials.csv"',
        ]);

        return $response;
    }

    protected function cleanupTempFile(?string $path): void
    {
        if ($path && file_exists($path)) {
            unlink($path);
        }
    }
}
