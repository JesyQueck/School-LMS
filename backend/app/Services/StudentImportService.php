<?php

namespace App\Services;

use App\Mail\EnrollmentNotification;
use App\Models\AcademicSession;
use App\Models\ParentProfile;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as SpreadsheetDate;

class StudentImportService
{
    public const REQUIRED_COLUMNS = [
        'admission_no',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'admission_date',
        'academic_session',
        'class',
        'student_type',
        'parent_name',
        'parent_relationship',
        'parent_phone',
    ];

    public const OPTIONAL_COLUMNS = [
        'middle_name',
        'nationality',
        'state_of_origin',
        'lga',
        'religion',
        'student_phone',
        'student_email',
        'address',
        'city',
        'state',
        'previous_school',
        'previous_school_address',
        'previous_class',
        'previous_year',
        'parent_whatsapp',
        'parent_email',
        'parent_occupation',
        'parent_address',
        'parent_city',
        'parent_state',
    ];

    public const VALID_GENDERS = ['male', 'female', 'other'];

    public const VALID_STUDENT_TYPES = ['new', 'returning', 'transfer'];

    protected string $tempPath;

    public function preview(string $filePath): array
    {
        $rows = $this->parseSpreadsheet($filePath);

        $validRows = collect();
        $invalidRows = collect();
        $warnings = collect();

        $seenAdmissionNos = [];
        $seenEmails = [];
        $existingAdmissionNos = Student::pluck('admission_no')->toArray();
        $validSessions = AcademicSession::pluck('id', 'name')->toArray();
        $validClasses = SchoolClass::pluck('id', 'name')->toArray();

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $errors = $this->validateRow($row, $rowNumber, $seenAdmissionNos, $seenEmails, $existingAdmissionNos, $validSessions, $validClasses);

            if ($errors->isNotEmpty()) {
                $invalidRows->push([
                    'row_number' => $rowNumber,
                    'data' => $row,
                    'errors' => $errors,
                ]);
            } else {
                $validRows->push([
                    'row_number' => $rowNumber,
                    'data' => $row,
                ]);

                if (isset($row['admission_no'])) {
                    $seenAdmissionNos[] = $row['admission_no'];
                }

                if (isset($row['student_email']) && ! empty($row['student_email'])) {
                    $seenEmails[] = strtolower($row['student_email']);
                }
            }
        }

        return [
            'total_rows' => $rows->count(),
            'valid_rows' => $validRows,
            'invalid_rows' => $invalidRows,
            'warnings' => $warnings,
        ];
    }

    public function import(Collection $validRows): array
    {
        $stats = [
            'imported' => 0,
            'parents_created' => 0,
            'parents_reused' => 0,
            'skipped' => 0,
            'errors' => collect(),
            'credentials' => collect(),
        ];

        DB::beginTransaction();

        try {
            foreach ($validRows as $row) {
                $data = $row['data'];

                $session = AcademicSession::where('name', $data['academic_session'])->first();
                $class = SchoolClass::where('name', $data['class'])->first();

                if (! $session || ! $class) {
                    $stats['errors']->push([
                        'row_number' => $row['row_number'],
                        'field' => 'class',
                        'value' => $data['class'],
                        'error' => 'Class or session no longer exists',
                    ]);

                    continue;
                }

                $parent = $this->findOrCreateParent($data, $stats);

                $studentEmail = $data['student_email'] ?? null;
                $studentProvidedEmail = ! empty($studentEmail);

                if (empty($studentEmail)) {
                    $studentEmail = $data['admission_no'].'@'.config('school.email_domain', 'school.local');
                }

                $existingUser = User::where('email', $studentEmail)->first();

                if ($existingUser) {
                    $stats['errors']->push([
                        'row_number' => $row['row_number'],
                        'field' => 'student_email',
                        'value' => $studentEmail,
                        'error' => 'Student email already in use',
                    ]);

                    continue;
                }

                $temporaryPassword = $this->generateStudentPassword($data);

                $user = User::create([
                    'name' => trim($data['first_name'].' '.$data['last_name']),
                    'email' => $studentEmail,
                    'phone' => $data['student_phone'] ?? null,
                    'password' => Hash::make($temporaryPassword),
                    'role' => 'student',
                    'is_active' => true,
                    'needs_password_change' => true,
                ]);

                $stats['credentials']->push([
                    'role' => 'student',
                    'name' => trim($data['first_name'].' '.$data['last_name']),
                    'email' => $studentEmail,
                    'password' => $temporaryPassword,
                    'related_to' => $parent ? ($parent->user->name ?? '') : null,
                    'user_id' => $user->id,
                    'was_email_provided' => $studentProvidedEmail,
                ]);

                $student = Student::create([
                    'user_id' => $user->id,
                    'first_name' => $data['first_name'],
                    'middle_name' => $data['middle_name'] ?? null,
                    'last_name' => $data['last_name'],
                    'admission_no' => $data['admission_no'],
                    'class_id' => $class->id,
                    'admission_date' => $data['admission_date'],
                    'academic_session_id' => $session->id,
                    'student_type' => $data['student_type'],
                    'previous_school' => $data['previous_school'] ?? null,
                    'previous_school_address' => $data['previous_school_address'] ?? null,
                    'previous_class' => $data['previous_class'] ?? null,
                    'previous_year_attended' => $data['previous_year'] ?? null,
                    'gender' => $data['gender'],
                    'date_of_birth' => $data['date_of_birth'],
                    'nationality' => $data['nationality'] ?? null,
                    'state_of_origin' => $data['state_of_origin'] ?? null,
                    'lga' => $data['lga'] ?? null,
                    'religion' => $data['religion'] ?? null,
                    'home_address' => $data['address'] ?? null,
                    'city' => $data['city'] ?? null,
                    'state' => $data['state'] ?? null,
                    'emergency_contact' => $parent ? null : null,
                    'emergency_phone' => $parent ? null : null,
                    'status' => 'active',
                ]);

                if ($parent) {
                    $student->parents()->attach($parent->id);
                }

                $studentName = trim($data['first_name'].' '.$data['last_name']);

                try {
                    Mail::to($user->email)->send(new EnrollmentNotification(
                        $user,
                        $temporaryPassword,
                        $parent ? ($parent->user->name ?? '') : '',
                    ));
                } catch (\Exception $e) {
                    $stats['errors']->push([
                        'row_number' => $row['row_number'],
                        'field' => 'email',
                        'value' => $user->email,
                        'error' => 'Email delivery failed: '.$e->getMessage(),
                    ]);

                    $stats['emails_failed'] = ($stats['emails_failed'] ?? 0) + 1;
                }

                if ($parent) {
                    $parentPassword = $stats['credentials']->firstWhere('role', 'parent')['password'] ?? null;
                    if ($parentPassword) {
                        try {
                            Mail::to($parent->user->email)->send(new EnrollmentNotification(
                                $parent->user,
                                $parentPassword,
                                $studentName,
                            ));
                        } catch (\Exception $e) {
                            $stats['emails_failed'] = ($stats['emails_failed'] ?? 0) + 1;
                        }
                    }
                }

                $stats['emails_sent'] = ($stats['emails_sent'] ?? 0) + ($parent ? 2 : 1);
                $stats['imported']++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $stats['errors']->push([
                'row_number' => 'N/A',
                'field' => 'system',
                'value' => '',
                'error' => $e->getMessage(),
            ]);
        }

        return $stats;
    }

    protected function parseSpreadsheet(string $filePath): Collection
    {
        $reader = IOFactory::createReaderForFile($filePath);

        $spreadsheet = $reader->load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        $rows = collect();
        $headers = null;

        foreach ($worksheet->getRowIterator() as $rowIndex => $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $cellValues = [];
            $cells = [];
            foreach ($cellIterator as $cell) {
                $cellValues[] = $cell->getValue();
                $cells[] = $cell;
            }

            if ($rowIndex === 1) {
                $headers = $cellValues;

                continue;
            }

            if (count(array_filter($cellValues, fn ($v) => $v !== null && $v !== '')) === 0) {
                continue;
            }

            $rowData = [];
            foreach ($headers as $colIndex => $header) {
                $header = strtolower(trim($header));
                $rawValue = isset($cellValues[$colIndex]) ? $cellValues[$colIndex] : null;
                $cell = $cells[$colIndex] ?? null;
                $rowData[$header] = $this->normalizeFieldValue($header, $rawValue, $cell);
            }

            $rows->push($rowData);
        }

        return $rows;
    }

    protected function normalizeFieldValue(string $field, mixed $value, mixed $cell = null): mixed
    {
        if (in_array($field, ['date_of_birth', 'admission_date'])) {
            return $this->normalizeDate($value, $cell);
        }

        if ($value === null) {
            return null;
        }

        return $this->normalizeScalarField($field, $value);
    }

    protected function normalizeScalarField(string $field, mixed $value): mixed
    {
        if (in_array($field, ['admission_no', 'parent_phone', 'parent_whatsapp', 'student_phone', 'previous_year'])) {
            $stringValue = (string) $value;

            return $stringValue === '' ? null : $stringValue;
        }

        if (is_string($value)) {
            return $value === '' ? null : $value;
        }

        return $value;
    }

    public function normalizeDate(mixed $value, mixed $cell = null): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_numeric($value) && $cell !== null && $cell instanceof Cell) {
            $formatCode = $cell->getStyle()->getNumberFormat()->getFormatCode();

            if (SpreadsheetDate::isDateTimeFormatCode($formatCode)) {
                $dateTime = SpreadsheetDate::excelToDateTimeObject((float) $value);

                return $dateTime->format('Y-m-d');
            }
        }

        if (is_numeric($value)) {
            $floatValue = (float) $value;

            if ($floatValue > 1 && $floatValue < 100000) {
                $dateTime = SpreadsheetDate::excelToDateTimeObject($floatValue);

                return $dateTime->format('Y-m-d');
            }
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            if ($trimmed === '') {
                return null;
            }

            $d = \DateTime::createFromFormat('Y-m-d', $trimmed);

            if ($d && $d->format('Y-m-d') === $trimmed) {
                return $trimmed;
            }

            $d = \DateTime::createFromFormat('Y-m-d H:i:s', $trimmed);

            if ($d && $d->format('Y-m-d') === substr($trimmed, 0, 10)) {
                return $d->format('Y-m-d');
            }

            $timestamps = [
                \DateTime::createFromFormat('m/d/Y', $trimmed),
                \DateTime::createFromFormat('d/m/Y', $trimmed),
                \DateTime::createFromFormat('Y/m/d', $trimmed),
            ];

            foreach ($timestamps as $ts) {
                if ($ts && $ts->format('Y-m-d') === $trimmed) {
                    return $trimmed;
                }
            }
        }

        return null;
    }

    protected function validateRow(
        array $row,
        int $rowNumber,
        array &$seenAdmissionNos,
        array &$seenEmails,
        array $existingAdmissionNos,
        array $validSessions,
        array $validClasses
    ): Collection {
        $errors = collect();

        foreach (self::REQUIRED_COLUMNS as $column) {
            if (! isset($row[$column]) || $row[$column] === null || trim((string) $row[$column]) === '') {
                $errors->push([
                    'field' => $column,
                    'value' => $row[$column] ?? '',
                    'error' => ucfirst(str_replace('_', ' ', $column)).' is required',
                ]);
            }
        }

        $gender = $row['gender'] ?? null;
        if ($gender && ! in_array(strtolower($gender), self::VALID_GENDERS, true)) {
            $errors->push([
                'field' => 'gender',
                'value' => $gender,
                'error' => 'Invalid gender. Expected: male, female, or other',
            ]);
        }

        $studentType = $row['student_type'] ?? null;
        if ($studentType && ! in_array(strtolower($studentType), self::VALID_STUDENT_TYPES, true)) {
            $errors->push([
                'field' => 'student_type',
                'value' => $studentType,
                'error' => 'Invalid student type. Expected: new, returning, or transfer',
            ]);
        }

        $dob = $row['date_of_birth'] ?? null;
        if (isset($row['date_of_birth']) && ! $this->isValidDate($dob)) {
            $errors->push([
                'field' => 'date_of_birth',
                'value' => $row['date_of_birth'] ?? '',
                'error' => 'Invalid date format. Expected: YYYY-MM-DD',
            ]);
        }

        $admissionDate = $row['admission_date'] ?? null;
        if (isset($row['admission_date']) && ! $this->isValidDate($admissionDate)) {
            $errors->push([
                'field' => 'admission_date',
                'value' => $row['admission_date'] ?? '',
                'error' => 'Invalid date format. Expected: YYYY-MM-DD',
            ]);
        }

        $email = $row['student_email'] ?? null;
        if ($email && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors->push([
                'field' => 'student_email',
                'value' => $email,
                'error' => 'Invalid email address format',
            ]);
        }

        $parentEmail = $row['parent_email'] ?? null;
        if ($parentEmail && ! filter_var($parentEmail, FILTER_VALIDATE_EMAIL)) {
            $errors->push([
                'field' => 'parent_email',
                'value' => $parentEmail,
                'error' => 'Invalid email address format',
            ]);
        }

        $admissionNo = $row['admission_no'] ?? null;
        if ($admissionNo) {
            if (in_array($admissionNo, $seenAdmissionNos, true)) {
                $errors->push([
                    'field' => 'admission_no',
                    'value' => $admissionNo,
                    'error' => 'Duplicate admission number within this file',
                ]);
            } elseif (in_array($admissionNo, $existingAdmissionNos, true)) {
                $errors->push([
                    'field' => 'admission_no',
                    'value' => $admissionNo,
                    'error' => 'Admission number already exists in the system',
                ]);
            }
        }

        $studentEmailLower = $email ? strtolower($email) : null;
        if ($studentEmailLower) {
            if (in_array($studentEmailLower, $seenEmails, true)) {
                $errors->push([
                    'field' => 'student_email',
                    'value' => $email,
                    'error' => 'Duplicate student email within this file',
                ]);
            } else {
                $existingStudent = User::where('email', $studentEmailLower)->where('role', 'student')->first();
                if ($existingStudent) {
                    $errors->push([
                        'field' => 'student_email',
                        'value' => $email,
                        'error' => 'Student email already exists in the system',
                    ]);
                }
                $seenEmails[] = $studentEmailLower;
            }
        }

        $sessionName = $row['academic_session'] ?? null;
        if ($sessionName && ! isset($validSessions[$sessionName])) {
            $suggestion = $this->findClosestMatch($sessionName, array_keys($validSessions));
            $errors->push([
                'field' => 'academic_session',
                'value' => $sessionName,
                'error' => 'Academic session does not exist',
                'suggestion' => $suggestion,
            ]);
        }

        $className = $row['class'] ?? null;
        if ($className && ! isset($validClasses[$className])) {
            $suggestion = $this->findClosestMatch($className, array_keys($validClasses));
            $errors->push([
                'field' => 'class',
                'value' => $className,
                'error' => 'Class does not exist',
                'suggestion' => $suggestion,
            ]);
        }

        return $errors;
    }

    protected function findOrCreateParent(array $data, array &$stats): ?ParentProfile
    {
        $parentEmail = $data['parent_email'] ?? null;
        $parentPhone = $data['parent_phone'] ?? null;

        $parent = null;

        if ($parentEmail) {
            $parent = ParentProfile::whereHas('user', fn ($q) => $q->where('email', $parentEmail))->first();
        }

        if (! $parent && $parentPhone) {
            $parent = ParentProfile::where('phone', $parentPhone)->first();
        }

        if ($parent) {
            $parent->user()->update([
                'phone' => $parentPhone ?? $parent->user->phone,
            ]);

            $parent->update([
                'first_name' => $data['parent_first_name'] ?? ($parent->first_name ?? null),
                'last_name' => $data['parent_last_name'] ?? ($parent->last_name ?? null),
                'phone' => $parentPhone ?? $parent->phone,
                'relationship_to_student' => $data['parent_relationship'] ?? $parent->relationship_to_student,
                'whatsapp' => $data['parent_whatsapp'] ?? $parent->whatsapp,
                'occupation' => $data['parent_occupation'] ?? $parent->occupation,
                'address' => $data['parent_address'] ?? $parent->address,
                'city' => $data['parent_city'] ?? $parent->city,
                'state' => $data['parent_state'] ?? $parent->state,
            ]);

            $stats['parents_reused']++;

            return $parent;
        }

        $parentPassword = $this->generateParentPassword($data);

        $parentUser = User::create([
            'name' => $data['parent_name'],
            'email' => $parentEmail ?? Str::slug($data['parent_name'], '.').'.'.Str::random(6).'@placeholder.local',
            'password' => Hash::make($parentPassword),
            'phone' => $parentPhone ?? null,
            'role' => 'parent',
            'is_active' => true,
            'needs_password_change' => true,
        ]);

        $parent = ParentProfile::create([
            'user_id' => $parentUser->id,
            'first_name' => $data['parent_first_name'] ?? null,
            'last_name' => $data['parent_last_name'] ?? null,
            'phone' => $parentPhone ?? null,
            'relationship_to_student' => $data['parent_relationship'] ?? null,
            'whatsapp' => $data['parent_whatsapp'] ?? null,
            'occupation' => $data['parent_occupation'] ?? null,
            'address' => $data['parent_address'] ?? null,
            'city' => $data['parent_city'] ?? null,
            'state' => $data['parent_state'] ?? null,
        ]);

        $stats['parents_created']++;
        $stats['credentials']->push([
            'role' => 'parent',
            'name' => $data['parent_name'] ?? 'Parent',
            'email' => $parentUser->email,
            'password' => $parentPassword,
            'related_to' => trim($data['first_name'].' '.$data['last_name']),
            'user_id' => $parentUser->id,
        ]);

        return $parent;
    }

    protected function generateStudentPassword(array $data): string
    {
        $lastName = strtolower(trim($data['last_name'] ?? ''));

        if (! empty($lastName) && preg_match('/^[a-zA-Z]+$/', $lastName)) {
            return $lastName;
        }

        return Str::random(12);
    }

    protected function generateParentPassword(array $data): string
    {
        $lastName = strtolower(trim($data['parent_last_name'] ?? ''));

        if (empty($lastName)) {
            $parentName = $data['parent_name'] ?? '';
            $parts = explode(' ', trim($parentName));
            if (count($parts) >= 2) {
                $lastName = strtolower(end($parts));
            }
        }

        if (! empty($lastName) && preg_match('/^[a-zA-Z]+$/', $lastName)) {
            return $lastName;
        }

        return Str::random(12);
    }

    protected function isValidDate(string $date): bool
    {
        try {
            $d = \DateTime::createFromFormat('Y-m-d', $date);

            return $d && $d->format('Y-m-d') === $date;
        } catch (\Exception) {
            return false;
        }
    }

    protected function findClosestMatch(string $input, array $candidates): ?string
    {
        $input = strtolower(trim($input));
        $closest = null;
        $minDistance = PHP_INT_MAX;

        foreach ($candidates as $candidate) {
            similar_text($input, strtolower($candidate), $percent);
            if ($percent > 0 && $percent < 100 && $percent > $minDistance) {
                $minDistance = $percent;
                $closest = $candidate;
            }
        }

        return $closest;
    }

    public function getTempPath(): ?string
    {
        return $this->tempPath;
    }

    public function setTempPath(string $path): void
    {
        $this->tempPath = $path;
    }
}
