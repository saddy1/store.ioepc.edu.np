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
        ]);

        $token_no = $request->input('token_num');
        $txn_number = $request->input('txn_number');
        $txn_date = $request->input('txn_date');

        // Fetch the student based on ioe_r (roll_no)
        $student = Student::where('token_num', $token_no)->first();



        // Fetch the transaction based on txn_number and txn_date
        $payment = TransactionBank::where('txn_id', $txn_number)
            ->whereDate('date', $txn_date)
            ->where('status', 1) // Add this line to check status
            ->first();
          

        if ($payment) {
            
            if($payment->amount < $student->amount){
                return redirect()->back()->withErrors(['The transaction amount does not match the required amount.'])->withInput();
            }

            $student->payment_id = $payment->id; // Assuming txn_id is fillable in the Student model
            $student->save(); // Save the changes

            $payment->status = 2; // Update the payment status to 2
            $payment->save(); // Save the payment status change

            return redirect()->back()->with('success', 'Payment verified and transaction ID stored successfully.');
        } else {
            return redirect()->back()->withErrors(['No transaction Found with the provided TXN Number and TXN Date or This transaction has already been used.'])->withInput();
        }
    }
}
