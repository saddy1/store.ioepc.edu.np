<?php
// app/Http/Controllers/VoucherController.php

namespace App\Http\Controllers;

use App\Models\StudentDocument;
use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Storage;


class VoucherController extends Controller
{
    public function store(Request $request)
    {
        // Validate: require token_num, PDFs only, max 2MB
        $validated = $request->validate([
            'token_num'        => ['required', 'string', 'exists:students,token_num'],
            'payment_voucher'  => ['required', 'file', 'mimes:pdf', 'max:2048'], // 2048 KB = 2MB
            'token_slip'       => ['required', 'file', 'mimes:pdf', 'max:2048'],
        ]);
        $request->validate([
            'payment_voucher' => ['required', 'file', 'mimes:pdf', 'max:2048'],
            'token_slip'      => ['required', 'file', 'mimes:pdf', 'max:2048'],
        ], [
            'payment_voucher.mimes' => 'Payment voucher must be a PDF file.',
            'payment_voucher.max'   => 'Payment voucher must be less than 2 MB.',
            'token_slip.mimes'      => 'Confirmation slip must be a PDF file.',
            'token_slip.max'        => 'Confirmation slip must be less than 2 MB.',
        ]);

        // Sanitize folder name (token_num)
        $rawToken = (string) $validated['token_num'];
        $token    = preg_replace('/[^A-Za-z0-9_\-]/', '', $rawToken);

        // Directory under the 'public' disk
        $dir = "students/{$token}";

        // (Optional) ensure directory exists
        if (!Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir);
        }

        // Save files with stable names (overwrite old versions if re-uploaded)
        $paymentName = "payment_voucher.pdf";
        $voucherName = "confirmation_slip.pdf";

        $paymentPath = $request->file('payment_voucher')->storeAs($dir, $paymentName, 'public');
        $voucherPath = $request->file('token_slip')->storeAs($dir, $voucherName, 'public');

        // Upsert by token_num (PK)
        StudentDocument::updateOrCreate(
            ['token_num' => $rawToken], // keep original token string for FK
            [
                'payment_image' => $paymentPath,
                'voucher_image' => $voucherPath,
            ]
        );
        Student::updateOrCreate(
            ['token_num' => $rawToken], // keep original token string for FK
            [
                'status' => 'submitted',
            ]
        );

        // Public URLs (if you need to display/download later)
        // php artisan storage:link  (ensure once in your project!)
        $paymentUrl = Storage::disk('public')->url($paymentPath);
        $voucherUrl = Storage::disk('public')->url($voucherPath);

        return back()->with('success', 'Documents uploaded successfully.')
            ->with('payment_url', $paymentUrl)
            ->with('voucher_url', $voucherUrl);
    }
}
