<?php
// app/Http/Controllers/StudentController.php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{

    public function showImportForm(Request $request)
    {
        $allowedPerPage = [10, 20, 50, 100, 200];
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        $q = trim((string) $request->input('q', ''));

        $query = Student::query();

        if ($q !== '') {
            $query->where(function ($x) use ($q) {
                $x->where('token_num', 'like', "%{$q}%")
                    ->orWhere('roll_num', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%")
                    ->orWhere('batch', 'like', "%{$q}%")
                    ->orWhere('subject', 'like', "%{$q}%");
            });
        }

        $students = $query->latest()->paginate($perPage)->appends($request->query());

        return view('Backend.student.import', compact('students', 'perPage', 'allowedPerPage', 'q'));
    }



    public function CalculateAmount($batch, $subject, $fine)
    {
        if ($batch == '069' || $batch == '070' || $batch == '071' || $batch == '072' || $batch == '073' || $batch == '074' || $batch == '075') {
            if ($fine) {
                return 510 + 1000;
            } else {
                return 510;
            }
        } else if (($batch == '076' || $batch == '077' || $batch == '078' || $batch == '079') && $subject <= 2) {
            if ($fine) {
                return 1000 + 2000;
            } else {
                return 1000;
            }
        } else if (($batch == '076' || $batch == '077' || $batch == '078' || $batch == '079') && $subject > 2) {

            if ($fine) {
                return 1700 + 2000;
            } else {
                return 1700;
            }
        } else if ($batch == '080' && $subject <= 2) {
            if ($fine) {
                return 1600 + 2500;
            } else {
                return 1600;
            }
        } else if ($batch == '080' && $subject > 2) {
            if ($fine) {
                return 2750 + 2500;
            } else {
                return 2750;
            }
        } else if ($batch == '081' && $subject <= 2) {
            if ($fine) {
                return 1690 + 2500;
            } else {
                return 1690;
            }
        } else if ($batch == '081' && $subject > 2) {
            if ($fine) {
                return 2925 + 2500;
            } else {
                return 2925;
            }
        }
    }
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls'],
        ]);

        $file = $request->file('file');
        $ext  = strtolower($file->getClientOriginalExtension());
        $fine = (int) $request->input('fine', 0);



        $rows = [];

        // 1) CSV/TXT path (auto-detect delimiter: tab vs comma)
        if (in_array($ext, ['csv', 'txt'])) {
            $content = file_get_contents($file->getRealPath());

            // Try to detect delimiter (tabs are common in your sample)
            $firstLine = strtok($content, "\r\n");
            $delimiter = (substr_count($firstLine, "\t") > substr_count($firstLine, ",")) ? "\t" : ",";

            $lines = preg_split("/\r\n|\n|\r/", trim($content));
            foreach ($lines as $line) {
                if (trim($line) === '') continue;
                $cols = str_getcsv($line, $delimiter);
                $rows[] = $cols;
            }
        }
        // 2) XLS/XLSX path (requires maatwebsite/excel)
        else {
            if (!class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
                return back()->with('error', 'To import Excel files (.xls/.xlsx), please install: composer require maatwebsite/excel');
            }
            // inline import using ToCollection to avoid extra class file
            $rows = [];
            \Maatwebsite\Excel\Facades\Excel::import(new class($rows) implements \Maatwebsite\Excel\Concerns\ToCollection {
                public $rowsRef;
                public function __construct(&$rows)
                {
                    $this->rowsRef = &$rows;
                }
                public function collection(\Illuminate\Support\Collection $collection)
                {
                    foreach ($collection as $row) {
                        $this->rowsRef[] = $row->toArray();
                    }
                }
            }, $file);
        }

        // Process rows
        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        foreach ($rows as $i => $cols) {
            // Skip header if present: detect if first row has non-numeric in col1 or "token"
            if ($i === 0) {
                $maybeHeader = isset($cols[0]) ? strtolower(trim((string)$cols[0])) : '';
                if (!is_numeric($cols[0] ?? null) || str_contains($maybeHeader, 'token')) {
                    // header detected; skip
                    if (count($rows) > 1) continue;
                }
            }

            // Normalize array to at least 8 columns
            for ($k = 0; $k < 8; $k++) {
                if (!isset($cols[$k])) $cols[$k] = null;
            }

            [$c1, $c2, $c3, $c4, $c5, $c6, $c7, $c8] = $cols;

            // Col1: token
            $token = trim((string)$c1);
            if ($token === '') {
                $skipped++;
                continue;
            } // token is required

            // Col2: roll (normalize)
            $roll = strtoupper(preg_replace('/\s+/', '', (string)$c2));
            if ($roll === '') {
                $skipped++;
                continue;
            }

            // Ensure prefix PUR
            if (substr($roll, 0, 3) !== 'PUR') {
                $roll = 'PUR' . substr($roll, 3);
            }

            // Derive parts from roll: PUR075BEI001
            $prefix = substr($roll, 0, 3);             // PUR
            $digits = substr($roll, 3, 3);             // 075 -> we may use as subject fallback
            $prog   = substr($roll, 6, 3);             // BEI -> batch field
            // remainder (serial) not used: substr($roll, 9)

            // Col3: name
            $name = trim((string)$c3);

            // Col6: year
            $year = trim((string)$c6);

            // Col7: part
            $part = trim((string)$c7);

            // Col8: subject (preferred)
            $subjectFromCol = trim((string)$c8);
            $subjectFinal   = $subjectFromCol !== '' ? $subjectFromCol : $digits; // fallback to digits from roll

            // Batch from roll letters
            $faculty = $prog !== '' ? $prog : null;
            $batch = $digits !== '' ? $digits : null;

            try {
                // Upsert by token_num
                $existing = Student::where('token_num', $token)->first();
                $amount = $this->CalculateAmount($batch, $subjectFinal, $fine);
                $payload = [
                    'roll_num'   => $roll,
                    'name'       => $name ?: 'Unknown',
                    'faculty'      => $faculty ?: '',
                    'batch'      => $batch ?: '',
                    'subject'    => $subjectFinal ?: '',
                    'year'       => $year ?: '',
                    'part'       => $part ?: '',
                    'amount'     => $amount ?: 0,
                    'fine' => $fine ? true : false,
                    // keep payment_id/status if you want; or set defaults
                ];

                if ($existing) {
                    $skipped++;
                } else {
                    Student::create(array_merge(['token_num' => $token], $payload));
                    $inserted++;
                }
            } catch (\Throwable $e) {
                // log error and skip row
                \Log::error('Student import error on row ' . $i . ': ' . $e->getMessage(), ['row' => $cols]);
                $skipped++;
            }
        }

        return redirect()
            ->route('students.import.form', request()->only('per_page', 'q'))
            ->with('success', "Import completed. Inserted: $inserted, Updated: $updated, Skipped: $skipped");
    }
}
