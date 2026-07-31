<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeType;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\Term;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function index()
    {
        return view('admin.finance.index', [
            'feeTypes' => FeeType::with(['term', 'class'])->get(),
            'studentFees' => StudentFee::with(['student', 'feeType', 'term', 'payments'])->get(),
            'payments' => Payment::with(['studentFee', 'recordedBy'])->get(),
            'students' => Student::with('class')->get(),
            'terms' => Term::all(),
            'classes' => SchoolClass::all(),
        ]);
    }

    public function createFeeType(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric'],
            'term_id' => ['required', 'exists:terms,id'],
            'class_id' => ['nullable', 'exists:classes,id'],
        ]);

        FeeType::create($data);

        return redirect()->route('admin.finance')->with('status', 'Fee type created.');
    }

    public function createStudentFee(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'fee_type_id' => ['required', 'exists:fee_types,id'],
            'term_id' => ['required', 'exists:terms,id'],
            'amount_expected' => ['required', 'numeric'],
        ]);

        $studentFee = StudentFee::create($data);
        $studentFee->update(['status' => 'unpaid']);

        return redirect()->route('admin.finance')->with('status', 'Student fee created.');
    }

    public function createPayment(Request $request)
    {
        $data = $request->validate([
            'student_fee_id' => ['required', 'exists:student_fees,id'],
            'receipt_number' => ['required', 'string', 'max:255'],
            'amount_paid' => ['required', 'numeric'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'payment_date' => ['required', 'date'],
        ]);

        $studentFee = StudentFee::findOrFail($data['student_fee_id']);
        $data['recorded_by'] = $request->user()->id;

        $payment = Payment::create($data);

        $studentFee->update([
            'status' => 'paid',
            'amount_expected' => $studentFee->amount_expected,
        ]);

        return redirect()->route('admin.finance')->with('status', 'Payment recorded.');
    }
}
