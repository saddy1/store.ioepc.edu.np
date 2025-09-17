<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\VoucherController;

use GuzzleHttp\Middleware;

Route::get('/', [DashboardController::class, 'home'])->name('home');


// Student auth
Route::get('/login/student', [AuthController::class, 'showStudentLogin'])->name('student.login.form');
Route::post('/login/student', [AuthController::class, 'studentLogin'])->name('student.login');
// routes/web.php
Route::get('/bank/import-public', [BankController::class, 'importFromPublicFile'])
    ->name('bank.import.public');


// Admin auth
Route::get('/admin', [AuthController::class, 'showAdminLogin'])->name('admin.login.form');
Route::post('/login/admin', [AuthController::class, 'adminLogin'])->name('admin.login');



// student Routes

Route::group(['middleware' => 'student.auth'], function () {
    Route::get('/dashboard/student', [DashboardController::class, 'student'])->name('student.dashboard');

    // Only logged-in student with matching roll can access
    Route::get('/form/{roll}', [FormController::class, 'showForm'])->name('form.show');

    // Only logged-in student with matching token can access
    Route::get('/verify-form/{token}', [FormController::class, 'VerifyForm'])->name('form.verify');

    Route::post('/verify-payment', [BankController::class, 'verify_payment'])->name('verify.payment');

    Route::post('/vouchers', [VoucherController::class, 'store'])->name('vouchers.store');
});










Route::group(['middleware' => 'admin.auth'], function () {
    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->name('admin.dashboard');

Route::get('/backend/bank', [BankController::class, 'index'])->name('bank.index');
    Route::get('/import', [BankController::class, 'importForm'])->name('import.form');
    Route::post('/import', [BankController::class, 'import'])->name('bank.import');
    

    Route::get('/students/import', [StudentController::class, 'showImportForm'])->name('students.import.form');
    Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');

    Route::get('/backend/applications', [VoucherController::class, 'show_application_form'])
     ->name('applications.index');

Route::post('/backend/applications/{token_num}/approve', [VoucherController::class, 'approve'])
     ->name('applications.approve');

});




// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
