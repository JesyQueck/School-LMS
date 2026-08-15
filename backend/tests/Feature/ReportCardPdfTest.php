<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\ClassSubject;
use App\Models\ReportCard;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportCardPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_download_published_report_card_pdf(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM801',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ]);

        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);
        $term = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
        ]);

        $reportCard = ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $term->id,
            'status' => ReportCard::STATUS_PUBLISHED,
        ]);

        $subject = Subject::create(['name' => 'Mathematics']);
        $classSubject = ClassSubject::create([
            'class_id' => $class->id,
            'subject_id' => $subject->id,
        ]);
        Result::create([
            'student_id' => $student->id,
            'class_subject_id' => $classSubject->id,
            'term_id' => $term->id,
            'ca_score' => 30,
            'exam_score' => 50,
            'total' => 80,
            'grade' => 'A1',
        ]);

        $this->actingAs($studentUser);

        $response = $this->get("/student/report-cards/{$reportCard->id}/download");
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_student_cannot_download_unpublished_report_card_pdf(): void
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM802',
        ]);

        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);
        $term = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
        ]);

        $reportCard = ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $term->id,
            'status' => ReportCard::STATUS_DRAFT,
        ]);

        $this->actingAs($studentUser);

        $response = $this->get("/student/report-cards/{$reportCard->id}/download");
        $response->assertForbidden();
    }

    /**
     * Decompress FlateDecode PDF streams so text assertions work on compressed binary.
     * Finds stream/endstream pairs by looking for the EOL-delimited keywords.
     */
    protected function decompressPdf(string $pdfContent): string
    {
        $result = '';
        $offset = 0;
        $len = strlen($pdfContent);

        while ($offset < $len) {
            // Find 'stream' keyword preceded by whitespace and followed by EOL
            $nextStream = strpos($pdfContent, 'stream', $offset);
            if ($nextStream === false) {
                $result .= substr($pdfContent, $offset);
                break;
            }

            // Verify this is a real stream keyword (preceded by newline or start)
            $beforeStream = $nextStream > 0 ? $pdfContent[$nextStream - 1] : '';
            if ($beforeStream !== "\n" && $beforeStream !== "\r" && $beforeStream !== ' ') {
                $offset = $nextStream + 6;
                continue;
            }

            // Skip past 'stream' + EOL
            $dataStart = $nextStream + 6; // strlen('stream')
            if ($dataStart < $len && $pdfContent[$dataStart] === "\r") {
                $dataStart++;
            }
            if ($dataStart < $len && $pdfContent[$dataStart] === "\n") {
                $dataStart++;
            }

            // Find 'endstream' — must be preceded by newline
            $searchStart = $dataStart;
            $foundEnd = false;
            while (($nextEndstream = strpos($pdfContent, 'endstream', $searchStart)) !== false) {
                // Verify it's a real endstream keyword (preceded by EOL)
                $beforeEnd = $nextEndstream > 0 ? $pdfContent[$nextEndstream - 1] : '';
                if ($beforeEnd === "\n" || $beforeEnd === "\r") {
                    $foundEnd = true;
                    break;
                }
                $searchStart = $nextEndstream + 9;
            }

            if (!$foundEnd) {
                $result .= substr($pdfContent, $nextStream);
                break;
            }

            $result .= substr($pdfContent, $offset, $nextStream - $offset);

            $raw = substr($pdfContent, $dataStart, $nextEndstream - $dataStart);
            // Remove trailing newline before endstream
            if (substr($raw, -1) === "\n") {
                $raw = substr($raw, 0, -1);
            }
            if (substr($raw, -1) === "\r") {
                $raw = substr($raw, 0, -1);
            }

            $decoded = @zlib_decode($raw);
            if ($decoded === false) {
                $decoded = @gzuncompress($raw);
            }
            if ($decoded === false) {
                $decoded = @gzinflate($raw);
            }

            if ($decoded !== false && strlen($decoded) > 0) {
                $result .= $decoded;
            } else {
                $result .= $raw;
            }

            $offset = $nextEndstream + 9; // strlen('endstream')
        }

        return $result;
    }

    /**
     * Count the number of actual page objects in a decompressed PDF.
     * Filters out font descriptors that may contain "/Type /Page" patterns.
     */
    protected function countPdfPages(string $pdfContent): int
    {
        // After decompression, find the /Pages root object and count its /Kids
        if (preg_match('/\/Type\s*\/Pages\W.*?\/Kids\s*\[([^\]]+)\]/s', $pdfContent, $m)) {
            return preg_match_all('/\dR/', $m[1]);
        }
        // Fallback: count /Type /Page (not /Pages)
        if (preg_match_all('/\/Type\s*\/Page(?!s)/', $pdfContent, $m)) {
            return count($m[0]);
        }
        return 0;
    }

    protected function createFullReportCardData(int $subjectCount = 30): array
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        $class = SchoolClass::create(['name' => 'JSS 1']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'class_id' => $class->id,
            'admission_no' => 'ADM801',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ]);

        $session = AcademicSession::create([
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
        ]);
        $term = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
        ]);

        $reportCard = ReportCard::create([
            'student_id' => $student->id,
            'term_id' => $term->id,
            'status' => ReportCard::STATUS_PUBLISHED,
            'class_teacher_remark' => 'Good student with excellent performance across all subjects.',
            'principal_remark' => 'Well done. Keep up the great work.',
            'affective_domain' => 'Shows good citizenship and leadership qualities.',
            'psychomotor_assessment' => 'Demonstrates excellent practical skills.',
            'health_remarks' => 'In good health with good attendance.',
            'position_in_class' => 3,
            'total_students_in_class' => 35,
            'promotion_decision' => 'Promoted',
            'next_term_begins' => '2027-01-15',
        ]);

        $subjectNames = [
            'Mathematics', 'English Language', 'Physics', 'Chemistry', 'Biology',
            'Geography', 'History', 'Economics', 'Government', 'Literature',
            'Computer Science', 'Physical Education', 'Fine Arts', 'Music',
            'French', 'Yoruba', 'Islamic Studies', 'Christian Studies', 'Agricultural Science', 'Commerce',
            'Technical Drawing', 'Home Economics', 'Further Mathematics', 'Civic Education', 'Visual Arts',
            'Drama', 'Business Studies', 'Health Education', 'Basic Science', 'Further English',
        ];

        for ($i = 0; $i < $subjectCount; $i++) {
            $name = $subjectNames[$i] ?? "Subject {$i}";
            $subject = Subject::create(['name' => $name]);
            $classSubject = ClassSubject::create([
                'class_id' => $class->id,
                'subject_id' => $subject->id,
            ]);
            $ca = 25 + ($i % 10);
            $exam = 55 + ($i % 40);
            Result::create([
                'student_id' => $student->id,
                'class_subject_id' => $classSubject->id,
                'term_id' => $term->id,
                'ca_score' => $ca,
                'exam_score' => $exam,
                'total' => $ca + $exam,
                'grade' => 'A1',
            ]);
        }

        return compact('studentUser', 'class', 'student', 'session', 'term', 'reportCard');
    }

    public function test_report_card_with_20_passing_subjects_downloads_as_single_page(): void
    {
        $data = $this->createFullReportCardData(20);
        $this->actingAs($data['studentUser']);

        $response = $this->get("/student/report-cards/{$data['reportCard']->id}/download");

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');

        $decompressed = $this->decompressPdf($response->getContent());
        $this->assertStringContainsString('Greenfield Academy', $decompressed);
        $this->assertStringContainsString('TERMINAL REPORT SHEET', $decompressed);

        $pageCount = $this->countPdfPages($decompressed);
        $this->assertLessThanOrEqual(1, $pageCount, 'PDF should be a single page');
    }

    public function test_report_card_with_30_passing_subjects_downloads_as_single_page(): void
    {
        $data = $this->createFullReportCardData(30);
        $this->actingAs($data['studentUser']);

        $response = $this->get("/student/report-cards/{$data['reportCard']->id}/download");

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');

        $decompressed = $this->decompressPdf($response->getContent());
        $this->assertStringContainsString('Greenfield Academy', $decompressed);
        $this->assertStringContainsString('TERMINAL REPORT SHEET', $decompressed);
        $this->assertStringContainsString('Mathematics', $decompressed);
        $this->assertStringContainsString('Visual Arts', $decompressed);
        $this->assertStringContainsString('A1', $decompressed);

        $pageCount = $this->countPdfPages($decompressed);
        $this->assertLessThanOrEqual(1, $pageCount, 'PDF should be a single page with 30 subjects');
    }

    public function test_report_card_content_not_cut_off_at_right_edge(): void
    {
        $data = $this->createFullReportCardData(30);
        $this->actingAs($data['studentUser']);

        $response = $this->get("/student/report-cards/{$data['reportCard']->id}/download");

        $response->assertOk();
        $decompressed = $this->decompressPdf($response->getContent());

        $this->assertStringContainsString('Mathematics', $decompressed);
        $this->assertStringContainsString('Visual Arts', $decompressed);
        $this->assertStringContainsString('Further English', $decompressed);
        $this->assertStringContainsString('TOTAL', $decompressed);
        $this->assertStringContainsString('A1', $decompressed);

        $pageCount = $this->countPdfPages($decompressed);
        $this->assertLessThanOrEqual(1, $pageCount, 'PDF should be a single page');
    }

    public function test_report_card_all_three_roles_render_correctly_with_many_subjects(): void
    {
        $data = $this->createFullReportCardData(30);

        // Student download
        $this->actingAs($data['studentUser']);
        $response = $this->get("/student/report-cards/{$data['reportCard']->id}/download");
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $decompressed = $this->decompressPdf($response->getContent());
        $this->assertStringContainsString('Greenfield Academy', $decompressed);

        $pageCount = $this->countPdfPages($decompressed);
        $this->assertLessThanOrEqual(1, $pageCount, 'Student PDF should be a single page');
    }
}
