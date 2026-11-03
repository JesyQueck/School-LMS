<?php

namespace App\Http\Controllers\Bursar;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\AuditLog;
use App\Models\FeeType;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\Term;
use App\Services\FeeService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected FeeService $feeService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $currentTerm = Term::where('is_current', true)->with('academicSession')->first();
        $sessionName = $currentTerm && $currentTerm->academicSession ? $currentTerm->academicSession->name : 'Current Session';
        $termName = $currentTerm ? $currentTerm->name : 'Current Term';

        $finance = $this->feeService->financeSummary();
        $classSummary = $this->feeService->classSummary();

        $recentPayments = Payment::with(['studentFee.student.schoolClass', 'studentFee.feeType', 'studentFee.term', 'recordedBy'])
            ->latest('payment_date')
            ->limit(10)
            ->get();

        $recentActivity = AuditLog::with('user')
            ->whereIn('action', ['fee_type.created', 'payment.created'])
            ->latest()
            ->limit(8)
            ->get();

        $actionLabels = [
            'fee_type.created' => 'Fee type created',
            'payment.created' => 'Payment recorded',
        ];

        $outstandingFees = StudentFee::whereIn('status', ['unpaid', 'partial'])
            ->with(['student.schoolClass', 'feeType', 'term'])
            ->latest()
            ->limit(10)
            ->get();

        return view('bursar.dashboard', compact(
            'user',
            'sessionName',
            'termName',
            'finance',
            'classSummary',
            'recentPayments',
            'recentActivity',
            'actionLabels',
            'outstandingFees'
        ));
    }
}