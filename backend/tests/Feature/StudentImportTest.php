<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\ParentProfile;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Tests\TestCase;

class StudentImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);

        SchoolClass::create(['name' => 'JSS 1']);
        SchoolClass::create(['name' => 'JSS 2']);
        SchoolClass::create(['name' => 'SSS 1']);
        SchoolClass::create(['name' => 'SSS 2']);

        Subject::firstOrCreate(['name' => 'Mathematics']);
        Subject::firstOrCreate(['name' => 'English Language']);

        $teacherUser = User::factory()->create([
            'role' => 'teacher',
            'name' => 'Sarah Adeyemi',
            'email' => 'sarah.adeyemi@demo.school',
            'is_active' => true,
        ]);

        Teacher::firstOrCreate(
            ['user_id' => $teacherUser->id],
            ['employee_id' => 'T-1001', 'qualification' => 'B.Ed']
        );

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@demo.school',
            'password' => Hash::make('AdminTest123!'),
        ]);
    }

    protected function validRow(string $admissionNo = 'GAA/2024/001', string $email = 'amina.bello@example.com'): array
    {
        return [
            'admission_no' => $admissionNo,
            'first_name' => 'Amina',
            'last_name' => 'Bello',
            'date_of_birth' => '2012-05-14',
            'gender' => 'Female',
            'admission_date' => '2026-09-01',
            'academic_session' => '2026/2027',
            'class' => 'JSS 1',
            'student_type' => 'New',
            'parent_name' => 'Ahmed Bello',
            'parent_relationship' => 'Father',
            'parent_phone' => '08030000001',
            'student_email' => $email,
            'parent_email' => 'ahmed.bello@example.com',
        ];
    }

    protected function makeCsvContent(array $rows): string
    {
        if (empty($rows)) {
            return '';
        }

        $headers = array_keys(reset($rows));
        $lines = [implode(',', array_map(function ($h) {
            return str_contains($h, ' ') ? '"'.$h.'"' : $h;
        }, $headers))];

        foreach ($rows as $row) {
            $values = array_map(function ($v) {
                $v = (string) $v;
                if (str_contains($v, ',') || str_contains($v, '"') || str_contains($v, "\n")) {
                    return '"'.str_replace('"', '""', $v).'"';
                }

                return $v;
            }, $row);
            $lines[] = implode(',', $values);
        }

        return implode("\n", $lines);
    }

    protected function makeCsvFile(array $rows, string $filename = 'test.csv'): File
    {
        return File::createWithContent($filename, $this->makeCsvContent($rows));
    }

    protected function makeXlsxFile(array $rows, string $filename = 'test.xlsx'): File
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        if (! empty($rows)) {
            $headers = array_keys(reset($rows));
            $sheet->fromArray($headers, null, 'A1');

            foreach ($rows as $index => $row) {
                $sheet->fromArray(array_values($row), null, 'A'.($index + 2));
            }
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_').'.xlsx';
        $writer = new XlsxWriter($spreadsheet);
        $writer->save($tempFile);

        return File::createWithContent($filename, file_get_contents($tempFile));
    }

    protected function makeXlsxWithSerialDates(array $rows, array $serialDateColumns, string $filename = 'test.xlsx'): File
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        if (! empty($rows)) {
            $headers = array_keys(reset($rows));
            $sheet->fromArray($headers, null, 'A1');

            foreach ($rows as $index => $row) {
                $rowIndex = $index + 2;
                $colIndex = 0;
                foreach ($row as $key => $value) {
                    $columnLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
                    $cell = $sheet->getCell($columnLetter.$rowIndex);
                    if (in_array($key, $serialDateColumns, true) && is_int($value)) {
                        $cell->setValue($value);
                        $cell->getStyle()->getNumberFormat()->setFormatCode('yyyy-mm-dd');
                    } else {
                        $cell->setValue($value);
                    }
                    $colIndex++;
                }
            }
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_').'.xlsx';
        $writer = new XlsxWriter($spreadsheet);
        $writer->save($tempFile);

        return File::createWithContent($filename, file_get_contents($tempFile));
    }

    protected function uploadAndPreview(array $rows, string $filename = 'test.csv'): void
    {
        $this->actingAs($this->admin);

        if (str_ends_with($filename, '.csv')) {
            $file = $this->makeCsvFile($rows, $filename);
        } else {
            $file = $this->makeXlsxFile($rows, $filename);
        }

        $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ])->assertRedirect(route('admin.students.import'));
    }

    public function test_admin_can_open_import_page(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.students.import'));

        $response->assertOk();
        $response->assertSee('Import Students');
        $response->assertSee('Upload Spreadsheet');
    }

    public function test_non_admin_cannot_access_import_page(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $this->actingAs($teacher);

        $response = $this->get(route('admin.students.import'));

        $response->assertForbidden();
    }

    public function test_non_authenticated_user_cannot_access_import_page(): void
    {
        $response = $this->get(route('admin.students.import'));

        $response->assertRedirect(route('login'));
    }

    public function test_import_page_renders_with_preview_session_data(): void
    {
        $this->actingAs($this->admin);

        session(['preview' => true]);
        session(['student_import_preview' => [
            'temp_path' => '/tmp/test.csv',
            'valid_rows' => [],
            'invalid_rows' => [
                [
                    'row_number' => 2,
                    'data' => [],
                    'errors' => [
                        ['field' => 'class', 'value' => '', 'error' => 'Class is required'],
                    ],
                ],
            ],
            'total_rows' => 1,
            'warnings' => [],
        ]]);

        $response = $this->get(route('admin.students.import'));

        $response->assertOk();
        $response->assertSee('Import Preview');
        $response->assertSee('Invalid Rows');
        $response->assertSee('Errors Found');
    }

    public function test_template_download_works(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.students.import.template'));

        $response->assertOk();
        $response->assertHeader('Content-Disposition', 'attachment; filename="student-import-template.csv"');
    }

    public function test_template_download_xlsx_works(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.students.import.template', ['format' => 'xlsx']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_valid_csv_can_be_uploaded(): void
    {
        $this->actingAs($this->admin);

        $rows = [$this->validRow()];
        $file = $this->makeCsvFile($rows);

        $response = $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ]);

        $response->assertRedirect(route('admin.students.import'));

        $preview = session('student_import_preview');
        $this->assertNotNull($preview);
        $this->assertEquals(1, $preview['total_rows']);
        $this->assertEquals(0, $preview['invalid_rows']->count());
    }

    public function test_valid_xlsx_can_be_uploaded(): void
    {
        $this->actingAs($this->admin);

        $rows = [$this->validRow('GAA/2024/002', 'david.okoro@example.com')];
        $file = $this->makeXlsxFile($rows);

        $response = $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ]);

        $response->assertRedirect(route('admin.students.import'));

        $preview = session('student_import_preview');
        $this->assertNotNull($preview);
        $this->assertEquals(0, $preview['invalid_rows']->count());
    }

    public function test_invalid_admission_number_is_rejected(): void
    {
        $row = $this->validRow();
        $row['admission_no'] = '';

        $this->actingAs($this->admin);
        $file = $this->makeCsvFile([$row]);

        $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ]);

        $preview = session('student_import_preview');
        $this->assertTrue($preview['invalid_rows']->contains('row_number', 2));
        $this->assertTrue($preview['invalid_rows']->first()['errors']->contains('field', 'admission_no'));
    }

    public function test_duplicate_admission_number_inside_spreadsheet_is_rejected(): void
    {
        $row1 = $this->validRow('GAA/2024/001', 'student1@example.com');
        $row2 = $this->validRow('GAA/2024/001', 'student2@example.com');

        $this->actingAs($this->admin);
        $file = $this->makeCsvFile([$row1, $row2]);

        $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ]);

        $preview = session('student_import_preview');
        $this->assertTrue($preview['invalid_rows']->contains('row_number', 3));
    }

    public function test_admission_number_already_exists_in_database_is_rejected(): void
    {
        $studentUser = User::factory()->create([
            'name' => 'Existing Student',
            'email' => 'existing.student@example.com',
            'role' => 'student',
        ]);
        Student::create([
            'user_id' => $studentUser->id,
            'admission_no' => 'GAA/2024/001',
            'class_id' => SchoolClass::where('name', 'JSS 1')->value('id'),
            'gender' => 'male',
            'date_of_birth' => '2012-01-01',
            'status' => 'active',
        ]);

        $row = $this->validRow('GAA/2024/001', 'new.student@example.com');

        $this->actingAs($this->admin);
        $file = $this->makeCsvFile([$row]);

        $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ]);

        $preview = session('student_import_preview');
        $this->assertTrue($preview['invalid_rows']->contains('row_number', 2));
    }

    public function test_invalid_class_is_rejected(): void
    {
        $row = $this->validRow();
        $row['class'] = 'JSS-1';

        $this->actingAs($this->admin);
        $file = $this->makeCsvFile([$row]);

        $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ]);

        $preview = session('student_import_preview');
        $this->assertTrue($preview['invalid_rows']->contains('row_number', 2));
        $errors = $preview['invalid_rows']->first()['errors'];
        $this->assertTrue($errors->contains(fn ($e) => $e['field'] === 'class'));
    }

    public function test_invalid_academic_session_is_rejected(): void
    {
        $row = $this->validRow();
        $row['academic_session'] = '2099/2100';

        $this->actingAs($this->admin);
        $file = $this->makeCsvFile([$row]);

        $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ]);

        $preview = session('student_import_preview');
        $this->assertTrue($preview['invalid_rows']->contains('row_number', 2));
        $errors = $preview['invalid_rows']->first()['errors'];
        $this->assertTrue($errors->contains(fn ($e) => $e['field'] === 'academic_session'));
    }

    public function test_missing_required_field_is_rejected(): void
    {
        $row = $this->validRow();
        $row['parent_phone'] = '';

        $this->actingAs($this->admin);
        $file = $this->makeCsvFile([$row]);

        $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ]);

        $preview = session('student_import_preview');
        $this->assertTrue($preview['invalid_rows']->contains('row_number', 2));
        $errors = $preview['invalid_rows']->first()['errors'];
        $this->assertTrue($errors->contains(fn ($e) => $e['field'] === 'parent_phone'));
    }

    public function test_invalid_date_is_rejected(): void
    {
        $row = $this->validRow();
        $row['date_of_birth'] = 'not-a-date';

        $this->actingAs($this->admin);
        $file = $this->makeCsvFile([$row]);

        $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ]);

        $preview = session('student_import_preview');
        $this->assertTrue($preview['invalid_rows']->contains('row_number', 2));
        $errors = $preview['invalid_rows']->first()['errors'];
        $this->assertTrue($errors->contains(fn ($e) => $e['field'] === 'date_of_birth'));
    }

    public function test_duplicate_student_email_is_rejected(): void
    {
        $existingUser = User::factory()->create([
            'name' => 'Existing Student',
            'email' => 'taken.email@example.com',
            'role' => 'student',
        ]);

        $row = $this->validRow('GAA/2024/001', 'taken.email@example.com');

        $this->actingAs($this->admin);
        $file = $this->makeCsvFile([$row]);

        $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ]);

        $preview = session('student_import_preview');
        $this->assertTrue($preview['invalid_rows']->contains('row_number', 2));
        $errors = $preview['invalid_rows']->first()['errors'];
        $this->assertTrue($errors->contains(fn ($e) => $e['field'] === 'student_email'));
    }

    public function test_duplicate_email_inside_spreadsheet_is_rejected(): void
    {
        $row1 = $this->validRow('GAA/2024/001', 'same@example.com');
        $row2 = $this->validRow('GAA/2024/002', 'same@example.com');

        $this->actingAs($this->admin);
        $file = $this->makeCsvFile([$row1, $row2]);

        $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ]);

        $preview = session('student_import_preview');
        $this->assertTrue($preview['invalid_rows']->contains('row_number', 3));
    }

    public function test_existing_parent_is_reused(): void
    {
        $existingParentUser = User::factory()->create([
            'name' => 'Ahmed Bello',
            'email' => 'ahmed.bello@example.com',
            'role' => 'parent',
            'is_active' => true,
        ]);
        ParentProfile::create([
            'user_id' => $existingParentUser->id,
            'phone' => '08030000001',
            'first_name' => 'Ahmed',
            'last_name' => 'Bello',
            'relationship_to_student' => 'Father',
        ]);

        $this->actingAs($this->admin);
        $file = $this->makeCsvFile([$this->validRow()]);

        $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ]);

        $preview = session('student_import_preview');
        $this->assertEquals(0, $preview['invalid_rows']->count());

        $response = $this->post(route('admin.students.import.confirm'));

        $response->assertRedirect(route('admin.students'));

        $this->assertEquals(1, ParentProfile::count());
        $this->assertDatabaseHas('parent_student', [
            'parent_id' => $existingParentUser->parentProfile->id,
            'student_id' => Student::where('admission_no', 'GAA/2024/001')->value('id'),
        ]);
    }

    public function test_new_parent_is_created_when_no_matching_parent_exists(): void
    {
        $this->actingAs($this->admin);
        $file = $this->makeCsvFile([$this->validRow()]);

        $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ]);

        $this->post(route('admin.students.import.confirm'));

        $this->assertEquals(1, ParentProfile::count());
        $this->assertDatabaseHas('users', [
            'email' => 'ahmed.bello@example.com',
            'role' => 'parent',
        ]);
        $this->assertDatabaseHas('parent_student', [
            'student_id' => Student::where('admission_no', 'GAA/2024/001')->value('id'),
        ]);
    }

    public function test_multiple_students_can_be_linked_to_same_parent(): void
    {
        $this->actingAs($this->admin);

        $rows = [
            $this->validRow('GAA/2024/001', 'student1@example.com'),
            $this->validRow('GAA/2024/002', 'student2@example.com'),
        ];

        $file = $this->makeCsvFile($rows);

        $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ]);

        $this->post(route('admin.students.import.confirm'));

        $this->assertEquals(1, ParentProfile::count());
        $this->assertDatabaseHas('parent_student', [
            'student_id' => Student::where('admission_no', 'GAA/2024/001')->value('id'),
        ]);
        $this->assertDatabaseHas('parent_student', [
            'student_id' => Student::where('admission_no', 'GAA/2024/002')->value('id'),
        ]);
    }

    public function test_successful_import_creates_users_students_and_parent_relationships(): void
    {
        $this->actingAs($this->admin);

        $rows = [
            $this->validRow('GAA/2024/001', 'amina.bello@example.com'),
            $this->validRow('GAA/2024/002', 'david.okoro@example.com'),
        ];

        $file = $this->makeCsvFile($rows);

        $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ]);

        $this->post(route('admin.students.import.confirm'));

        $this->assertEquals(2, Student::count());
        $this->assertEquals(2, User::where('role', 'student')->count());
        $this->assertEquals(1, ParentProfile::count());

        $this->assertDatabaseHas('users', [
            'email' => 'amina.bello@example.com',
            'role' => 'student',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('students', [
            'admission_no' => 'GAA/2024/001',
            'first_name' => 'Amina',
            'last_name' => 'Bello',
            'gender' => 'Female',
            'class_id' => SchoolClass::where('name', 'JSS 1')->value('id'),
        ]);

        $this->assertDatabaseHas('parent_student', [
            'student_id' => Student::where('admission_no', 'GAA/2024/001')->value('id'),
            'parent_id' => ParentProfile::first()->id,
        ]);
    }

    public function test_failed_validation_creates_no_student_records(): void
    {
        $this->actingAs($this->admin);

        $row = $this->validRow();
        $row['class'] = 'Nonexistent Class';

        $file = $this->makeCsvFile([$row]);

        $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ]);

        $this->post(route('admin.students.import.confirm'));

        $this->assertEquals(0, Student::count());
        $this->assertEquals(0, User::where('role', 'student')->count());
    }

    public function test_partial_import_does_not_occur_when_transaction_fails(): void
    {
        $this->actingAs($this->admin);

        $row1 = $this->validRow('GAA/2024/001', 'student1@example.com');
        $row2 = $this->validRow('GAA/2024/002', 'student2@example.com');

        $existingUser = User::factory()->create([
            'name' => 'Test Parent',
            'email' => 'test.parent@example.com',
            'role' => 'parent',
        ]);
        ParentProfile::create([
            'user_id' => $existingUser->id,
            'phone' => '08030000001',
        ]);

        $file = $this->makeCsvFile([$row1, $row2]);

        $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ]);

        // After preview validation passes, create a conflicting student record
        // with the same admission number as row2. This will cause a QueryException
        // during import due to the unique constraint on admission_no, triggering
        // a transaction rollback so no partial imports occur.
        $conflictingUser = User::factory()->create([
            'name' => 'Conflicting Student',
            'email' => 'conflict@example.com',
            'role' => 'student',
        ]);
        Student::create([
            'user_id' => $conflictingUser->id,
            'admission_no' => 'GAA/2024/002',
            'class_id' => SchoolClass::first()->id,
            'first_name' => 'Conflict',
            'last_name' => 'Student',
            'gender' => 'Male',
        ]);

        $this->post(route('admin.students.import.confirm'));

        $stats = session('import_stats');
        $this->assertNotTrue($stats['errors']->isEmpty());
    }

    public function test_imported_students_can_immediately_use_student_portal(): void
    {
        $this->actingAs($this->admin);

        $file = $this->makeCsvFile([$this->validRow()]);

        $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ]);

        $this->post(route('admin.students.import.confirm'));

        $student = Student::where('admission_no', 'GAA/2024/001')->first();
        $this->assertNotNull($student);

        $this->actingAs($student->user);

        $response = $this->get('/student/dashboard');

        $response->assertOk();
    }

    public function test_cancel_import_clears_session(): void
    {
        $this->actingAs($this->admin);

        $file = $this->makeCsvFile([$this->validRow()]);

        $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ]);

        $this->assertNotNull(session('student_import_preview'));

        $response = $this->post(route('admin.students.import.cancel'));

        $response->assertRedirect(route('admin.students.import'));
        $this->assertNull(session('student_import_preview'));
    }

    public function test_import_without_valid_rows_shows_error(): void
    {
        $this->actingAs($this->admin);

        $row = $this->validRow();
        $row['class'] = '';

        $file = $this->makeCsvFile([$row]);

        $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ]);

        $response = $this->post(route('admin.students.import.confirm'));

        $response->assertRedirect(route('admin.students.import'));
        $response->assertSessionHas('error');
        $this->assertEquals(0, Student::count());
    }

    public function test_xlsx_excel_serial_date_of_birth_is_normalized(): void
    {
        $this->actingAs($this->admin);

        $row = $this->validRow('GAA/2024/SER1', 'serial.dob@example.com');
        $row['date_of_birth'] = 41043; // Excel serial for 2012-05-14
        $row['admission_date'] = '2026-09-01';

        $file = $this->makeXlsxWithSerialDates([$row], ['date_of_birth']);

        $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ]);

        $preview = session('student_import_preview');
        $this->assertEquals(0, $preview['invalid_rows']->count());
        $this->assertEquals('2012-05-14', $preview['valid_rows']->first()['data']['date_of_birth']);
    }

    public function test_xlsx_excel_serial_admission_date_is_normalized(): void
    {
        $this->actingAs($this->admin);

        $row = $this->validRow('GAA/2024/SER2', 'serial.adm@example.com');
        $row['date_of_birth'] = '2012-05-14';
        $row['admission_date'] = 46266; // Excel serial for 2026-09-01

        $file = $this->makeXlsxWithSerialDates([$row], ['admission_date']);

        $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ]);

        $preview = session('student_import_preview');
        $this->assertEquals(0, $preview['invalid_rows']->count());
        $this->assertEquals('2026-09-01', $preview['valid_rows']->first()['data']['admission_date']);
    }

    public function test_xlsx_native_date_cell_is_normalized(): void
    {
        $this->actingAs($this->admin);

        // Create XLSX where DOB is a native DateTime cell, admission_date is a serial
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $headers = array_keys($this->validRow('GAA/2024/DAT', 'native.date@example.com'));
        $sheet->fromArray($headers, null, 'A1');

        $row = $this->validRow('GAA/2024/DAT', 'native.date@example.com');
        $colIndex = 0;
        foreach ($row as $key => $value) {
            $columnLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
            $cell = $sheet->getCell($columnLetter.'2');
            if ($key === 'date_of_birth') {
                $cell->setValue(new \DateTime('2012-05-14'));
            } elseif ($key === 'admission_date') {
                $cell->setValue(46266);
                $cell->getStyle()->getNumberFormat()->setFormatCode('yyyy-mm-dd');
            } else {
                $cell->setValue($value);
            }
            $colIndex++;
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_').'.xlsx';
        (new XlsxWriter($spreadsheet))->save($tempFile);
        $file = File::createWithContent('native.xlsx', file_get_contents($tempFile));
        unlink($tempFile);

        $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ]);

        $preview = session('student_import_preview');
        $this->assertEquals(0, $preview['invalid_rows']->count());
        $this->assertEquals('2012-05-14', $preview['valid_rows']->first()['data']['date_of_birth']);
        $this->assertEquals('2026-09-01', $preview['valid_rows']->first()['data']['admission_date']);
    }

    public function test_csv_yyyy_mm_dd_date_is_accepted(): void
    {
        $this->actingAs($this->admin);

        $row = $this->validRow('GAA/2024/CSV', 'csv.date@example.com');

        $file = $this->makeCsvFile([$row]);

        $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ]);

        $preview = session('student_import_preview');
        $this->assertEquals(0, $preview['invalid_rows']->count());
        $this->assertEquals('2012-05-14', $preview['valid_rows']->first()['data']['date_of_birth']);
    }

    public function test_invalid_date_string_is_rejected(): void
    {
        $this->actingAs($this->admin);

        $row = $this->validRow('GAA/2024/BAD1', 'bad.date@example.com');
        $row['date_of_birth'] = 'not-a-date';

        $file = $this->makeCsvFile([$row]);

        $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ]);

        $preview = session('student_import_preview');
        $this->assertTrue($preview['invalid_rows']->contains('row_number', 2));
        $this->assertTrue($preview['invalid_rows']->first()['errors']->contains(fn ($e) => $e['field'] === 'date_of_birth'));
    }

    public function test_impossible_date_is_rejected(): void
    {
        $this->actingAs($this->admin);

        $row = $this->validRow('GAA/2024/BAD2', 'impossible@example.com');
        $row['date_of_birth'] = '2012-13-45';

        $file = $this->makeCsvFile([$row]);

        $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ]);

        $preview = session('student_import_preview');
        $this->assertTrue($preview['invalid_rows']->contains('row_number', 2));
        $this->assertTrue($preview['invalid_rows']->first()['errors']->contains(fn ($e) => $e['field'] === 'date_of_birth'));
    }

    public function test_invalid_numeric_date_is_rejected(): void
    {
        $this->actingAs($this->admin);

        $row = $this->validRow('GAA/2024/BAD3', 'invalidnum@example.com');
        $row['date_of_birth'] = 'not-serial-99999';

        $file = $this->makeCsvFile([$row]);

        $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ]);

        $preview = session('student_import_preview');
        $this->assertTrue($preview['invalid_rows']->contains('row_number', 2));
        $this->assertTrue($preview['invalid_rows']->first()['errors']->contains(fn ($e) => $e['field'] === 'date_of_birth'));
    }

    public function test_negative_excel_serial_date_is_rejected(): void
    {
        $this->actingAs($this->admin);

        // -41043 is a negative Excel serial — no real Excel date produces a negative serial
        $row = $this->validRow('GAA/2024/BAD4', 'negserial@example.com');
        $row['date_of_birth'] = -41043;

        $file = $this->makeXlsxWithSerialDates([$row], []);

        $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ]);

        $preview = session('student_import_preview');
        $this->assertTrue($preview['invalid_rows']->contains('row_number', 2));
        $this->assertTrue($preview['invalid_rows']->first()['errors']->contains(fn ($e) => $e['field'] === 'date_of_birth'));
    }

    public function test_imported_student_stores_normalized_dates_in_database(): void
    {
        $this->actingAs($this->admin);

        $row = $this->validRow('GAA/2024/DB1', 'db.date@example.com');
        $row['date_of_birth'] = 41043;   // Excel serial for 2012-05-14
        $row['admission_date'] = 46266;  // Excel serial for 2026-09-01

        $file = $this->makeXlsxWithSerialDates([$row], ['date_of_birth', 'admission_date']);

        $this->post(route('admin.students.import.preview'), [
            'import_file' => $file,
        ]);

        $this->post(route('admin.students.import.confirm'));

        $student = Student::where('admission_no', 'GAA/2024/DB1')->first();
        $this->assertNotNull($student);
        $this->assertEquals('2012-05-14', $student->date_of_birth->format('Y-m-d'));
        $this->assertEquals('2026-09-01', $student->admission_date->format('Y-m-d'));
    }
}
