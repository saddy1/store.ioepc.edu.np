<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BankDetailsImport;
use App\Models\TransactionBank;
use App\Models\Student;

class BankController extends Controller
{
    public function index(Request $request)
    {
        $transactionData = TransactionBank::all();
        return view('backend.bank.index', compact('transactionData'));
    }

    public function importForm()
    {
        return view('import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx',
        ]);

        try {
            $importedData = Excel::import(new BankDetailsImport, $request->file('file'));

            if ($importedData === null) {
                return redirect()->back()->with('error', 'INVALID OR WRONG A/C IMPORT FILE UPLOADED');
            }

            return redirect()->back()->with('success', 'Data imported successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing data: ' . $e->getMessage());
        }
    }

    public function verify_payment(Request $request)
    {
        // Validate the request data
        $request->validate([
            'txn_number' => 'required|string|max:255',
            'txn_date' => 'required|date',
            'roll_no' => 'required|string|max:255', // Assuming roll_no is the same as ioe_r
        ]);

        $roll_no = $request->input('roll_no');
        $txn_number = $request->input('txn_number');
        $txn_date = $request->input('txn_date');

        // Fetch the student based on ioe_r (roll_no)
        $student = Student::where('ioe_r', $roll_no)->first();

        if (!$student) {
            return redirect()->back()->withErrors(['Student not found.']);
        }

        // Fetch the transaction based on txn_number and txn_date
        $payment = TransactionBank::where('txn_id', $txn_number)
            ->whereDate('date', $txn_date)
            ->where('status', 1) // Add this line to check status
            ->first();

        if ($payment) {
            $studentAttributes = [
                $student->full_name,
                $student->ioe_r,
                $student->rank,
            ];

            $nameInTransaction = $payment->name; // Adjust this based on your TransactionBank model
            $isMatch = false;

            foreach ($studentAttributes as $attribute) {
                if (stripos($nameInTransaction, $attribute) !== false) {
                    $isMatch = true;
                    break;
                }
            }

            if ($isMatch) {
                $student->txn_id = $payment->id; // Assuming txn_id is fillable in the Student model
                $student->save(); // Save the changes
            
                $payment->status = 2; // Update the payment status to 2
                $payment->save(); // Save the payment status change
            
                session(['student' => $student]); // Update the session with the latest student data
                return redirect()->back()->with('success', 'Payment verified and transaction ID stored successfully.');
            }
            else {
                return redirect()->back()->withErrors(['No matching student details found in the transaction name.']);
            }
        } else {
            return redirect()->back()->withErrors(['Payment not found.']);
        }
    }
}
