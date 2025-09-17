<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\TransactionBank;

class BankDetailsImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $bankArr = [];

        // Validate if the fourth row, fifth column contains the specific account number
        if (isset($rows[4][4]) && ($rows[4][4] == "2319114258524002" || $rows[4][4] == "2319114258524003")) {

            // Iterate over each row of the collection
            foreach ($rows as $key => $row) {
                $bankDate = trim($row[2]);
                $bankParticular = str_replace("/", " ", trim($row[4]));
                $bankTxnID = trim($row[6]);

                // Extract numerical value from the string
                $str = preg_replace('/[^0-9.]/', '', $row[14]);
                $bankAmount = floatval($str);

                // ✅ Only import transactions between 500 and 5000
                if ($bankAmount < 500 || $bankAmount > 5000) {
                    continue;
                }

                // Prepare to check conditions
                if ($this->isValidTransaction($bankDate, $bankParticular, $bankTxnID, $bankAmount)) {
                    $DCNUMBER = $this->getDCNumber($bankParticular);
                    $bankArrTemp = $this->prepareTransactionArray($bankDate, $bankParticular, $bankAmount, $DCNUMBER, $bankTxnID);

                    // Insert if not already exists
                    if ($DCNUMBER != 'n/a' && !TransactionBank::where(['txn_id' => $DCNUMBER, 'date' => $bankDate])->exists()) {
                        TransactionBank::create($bankArrTemp);
                        $bankArr[] = $bankArrTemp;
                    } elseif ($DCNUMBER == 'n/a') {
                        $bankArr[] = $bankArrTemp;
                    }
                }
            }

            return $bankArr; // Return the inserted data for further processing
        }

        // If no valid account number is found
        return null;
    }

    private function isValidTransaction($bankDate, $bankParticular, $bankTxnID, $bankAmount)
    {
        $b = 0.00;
        $isPaymentForApp = $this->isNonApplicationFormPaisa($bankParticular);
        return $bankDate && $bankParticular && $bankTxnID && $isPaymentForApp && bccomp($bankAmount, $b, 3) != 0;
    }

    private function getDCNumber($bankParticular)
    {
        $isDCorS = $this->isDCorS($bankParticular);

        if (isset($isDCorS['DC']) && $isDCorS['DC'][0] === 'S') {
            // Split by comma
            $parts = explode(',', $bankParticular, 2);
            $beforeComma = $parts[0] ?? '';

            // Split by first '-'
            $subParts = explode('-', $beforeComma, 2);
            $result = $subParts[1] ?? '';

            return $result ?: 'n/a';
        }

        // If not starting with 'S', just return DC if available
        return $isDCorS['DC'] ?? 'n/a';
    }

    private function prepareTransactionArray($bankDate, $bankParticular, $bankAmount, $DCNUMBER, $bankTxnID)
    {
        return [
            'date' => $bankDate,
            'txn_id' => $DCNUMBER != 'n/a' ? $DCNUMBER : "**{$bankTxnID}**",
            'name' => $bankParticular,
            'amount' => $bankAmount,
            'status' => 1,
        ];
    }

    private function isDCorS($value)
    {
        $re = '/DC[0-9]+|S[0-9]+/m';
        if (preg_match($re, $value, $matches)) {
            return ['status' => true, 'DC' => $matches[0]];
        }
        return ['status' => false];
    }

    private function isNonApplicationFormPaisa($value)
    {
        $re = '/TRF TO CURRENT ACCOUNT|FROM CURRENT ACCOUNT|FRM CURRENT ACCOUNT/m';
        return !preg_match($re, $value);
    }
}
