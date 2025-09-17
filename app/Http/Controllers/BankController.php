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
    $allowed = [10,20,50,100,200];
    $perPage = (int) $request->input('per_page', 10);
    if (!in_array($perPage, $allowed)) $perPage = 10;

    $q      = trim((string) $request->input('q', ''));     // name/token/txn
    $date   = $request->input('date');                     // YYYY-MM-DD
    $status = $request->input('status');                   // 'used' | 'unused' | null

    $query = TransactionBank::query();

    // Filters
    if ($q !== '') {
        $query->where(function($x) use ($q) {
            $x->where('name', 'like', "%{$q}%")
              ->orWhere('amount', 'like', "%{$q}%")
              ->orWhere('txn_id', 'like', "%{$q}%");
        });
    }
    if (!empty($date)) {
        // adjust column name if your date column is different (e.g., txn_date)
        $query->whereDate('date', $date);
    }
    if ($status === 'used') {
        $query->where('status', 2);     // Used
    } elseif ($status === 'unused') {
        $query->where('status', 1);     // Unused
    }

    $transactions = $query->orderByDesc('date')->paginate($perPage)->appends($request->query());

    return view('Backend.bank.index', [
        'transactions'   => $transactions,
        'q'              => $q,
        'date'           => $date,
        'status'         => $status,
        'perPage'        => $perPage,
        'allowedPerPage' => $allowed,
    ]);
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


    public function importFromPublicFile()
{
    // Absolute path to: public/statement/NIC_ASIA_Statements/statement.xls
   $path = public_path('NIC_ASIA_Statements/statement.xls');

if (!file_exists($path)) {
    return response()->json([
        'status' => 'error',
        'message' => "File not found: {$path}"
    ], 404);
}

// Continue with your import
try {
    $importedData = Excel::import(new BankDetailsImport, $path);

    if ($importedData === null) {
        return response()->json([
            'status' => 'error',
            'message' => 'INVALID OR WRONG A/C IMPORT FILE UPLOADED'
        ], 400);
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Data imported successfully.'
    ], 200);

} catch (\Exception $e) {
    return response()->json([
        'status' => 'error',
        'message' => 'Error importing data: ' . $e->getMessage()
    ], 500);
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
