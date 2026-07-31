<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\CsvExportService;
use App\Traits\AuditsActions;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SchoolAdminController extends Controller
{
    use AuditsActions;

    public function __construct(
        protected CsvExportService $csvExportService,
    ) {}
    public function index()
    {
        return view('admin.index', [
            'classes' => SchoolClass::count(),
            'teachers' => Teacher::count(),
            'students' => Student::count(),
        ]);
    }

    public function classes()
    {
        return view('admin.classes.index', [
            'classes' => SchoolClass::with('formTeacher')->get(),
            'teachers' => Teacher::with('user')->get(),
        ]);
    }

    public function teachers()
    {
        return view('admin.teachers.index', [
            'teachers' => Teacher::with('user')->get(),
        ]);
    }

    public function students()
    {
        return view('admin.students.index', [
            'students' => Student::with(['user', 'class'])->get(),
            'classes' => SchoolClass::all(),
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

    public function createTeacher(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'specialization' => ['nullable', 'string', 'max:255'],
        ]);

        $data['qualification'] = $data['specialization'] ?? null;
        unset($data['specialization']);

        if (empty($data['employee_id'] ?? null)) {
            $data['employee_id'] = 'T-' . Str::upper(Str::random(6));
        }

        Teacher::create($data);

        $this->audit($request, 'teacher.created', Teacher::class, Teacher::query()->latest('id')->value('id'), null, $data);

        return redirect()->route('admin.teachers')->with('status', 'Teacher created.');
    }

    public function createStudent(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'class_id' => ['nullable', 'exists:classes,id'],
            'admission_number' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
        ]);

        if (empty($data['class_id'])) {
            $class = SchoolClass::query()->first();

            if (! $class) {
                $class = SchoolClass::create([
                    'name' => 'Default Class',
                ]);
            }

            $data['class_id'] = $class->id;
        }

        $data['admission_no'] = $data['admission_number'] ?? 'ADM-' . Str::upper(Str::random(6));
        unset($data['admission_number']);

        if (empty($data['class_id'] ?? null)) {
            $data['class_id'] = SchoolClass::query()->value('id') ?? SchoolClass::create(['name' => 'Default Class'])->id;
        }

        Student::create($data);

        $this->audit($request, 'student.created', Student::class, Student::query()->latest('id')->value('id'), null, $data);

        return redirect()->route('admin.students')->with('status', 'Student created.');
    }

    public function exportStudents(Request $request)
    {
        return $this->csvExportService->exportStudents($request);
    }
}
