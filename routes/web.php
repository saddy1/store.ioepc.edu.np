<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\StudentController;

use GuzzleHttp\Middleware;

Route::get('/', [DashboardController::class, 'home'])->name('home');


// Student auth
Route::get('/login/student', [AuthController::class, 'showStudentLogin'])->name('student.login.form');
Route::post('/login/student', [AuthController::class, 'studentLogin'])->name('student.login');


// Admin auth
Route::get('/admin', [AuthController::class, 'showAdminLogin'])->name('admin.login.form');
Route::post('/login/admin', [AuthController::class, 'adminLogin'])->name('admin.login');

Route::group(['middleware' => 'student.auth'], function () {
    Route::get('/dashboard/student', [DashboardController::class, 'student'])->name('student.dashboard');
});


Route::group(['middleware' => 'admin.auth'], function () {
    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->name('admin.dashboard');

    Route::get('/bank_data', [BankController::class, 'index'])->name('bank_data');
    Route::get('/import', [BankController::class, 'importForm'])->name('import.form');
    Route::post('/import', [BankController::class, 'import'])->name('import');

    Route::get('/students/import', [StudentController::class, 'showImportForm'])->name('students.import.form');
    Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');
});




// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
