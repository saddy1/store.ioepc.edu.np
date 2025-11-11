<?php

use App\Http\Controllers\BankController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BSCalendarController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/upload-bank-details', [BankController::class, 'uploadBankDetails']);

Route::get('/ad-to-bs',   [BSCalendarController::class, 'adToBs']);   // AD → BS conversion
Route::get('/bs-to-ad',   [BSCalendarController::class, 'bsToAd']);   // BS → AD conversion
Route::get('/bs-month',   [BSCalendarController::class, 'bsMonth']);  // Full month grid
