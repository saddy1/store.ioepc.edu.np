<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ItemCategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\controllers\DepartmentController;
use App\Http\Controllers\PurchaseSlipController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\BSCalendarController;
use App\Http\Controllers\StoreEntryController;


// Purchase Slips


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


    Route::resource('suppliers', SupplierController::class);
    Route::resource('categories', ItemCategoryController::class);
    Route::resource('brands', BrandController::class);
    Route::resource('department', DepartmentController::class);



    Route::resource('product_categories', CategoryController::class);
    // Route::resource('products', ProductController::class);

    // Route::get('slips', [PurchaseSlipController::class, 'index'])->name('slips.index'); // build later
    // Route::get('slips/create', [PurchaseSlipController::class, 'create'])->name('slips.create');
    // Route::post('slips', [PurchaseSlipController::class, 'store'])->name('slips.store');
    // Route::get('slips/{slip}', [PurchaseSlipController::class, 'show'])->name('slips.show');

    // Route::get('purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    // Route::get('purchases/create', [PurchaseController::class, 'create'])->name('purchases.create'); // needs ?slip_id=
    // Route::post('purchases', [PurchaseController::class, 'store'])->name('purchases.store');


    Route::prefix('store')->name('store.')->group(function () {
        Route::get('/',                             [StoreEntryController::class, 'index'])->name('index');
        Route::get('/{storeEntry}',                 [StoreEntryController::class, 'show'])->name('show');
        Route::get('/post/{purchase}/prepare',      [StoreEntryController::class, 'prepare'])->name('prepare');
        Route::post('/post/{purchase}',             [StoreEntryController::class, 'postFromPurchase'])->name('post-from-purchase');
        Route::get('/store/ledger/category/{category}', [StoreEntryController::class, 'ledgerByCategory'])
            ->name('ledger.category');

            route::get('/store/ledger',                 [StoreEntryController::class, 'ledger'])->name('ledger');
                Route::get('/categories/{category}/items',  [StoreEntryController::class, 'categoryItems'])->name('categories.items');

        Route::get('/categories/{category}',        [StoreEntryController::class, 'ledgerByCategory'])->name('categories.show');


         Route::get('/browse',                                                         [StoreEntryController::class, 'browseRoot'])->name('browse');
    Route::get('/browse/item-category',                                           [StoreEntryController::class, 'browseItemCategories'])->name('browse.ic');
    Route::get('/browse/item-category/{itemCategory}/product-categories',         [StoreEntryController::class, 'browseProductCategoriesUnderIC'])->name('browse.ic.categories');
    });


    Route::get('/slips/{slip}/print', [PurchaseSlipController::class, 'print'])
        ->name('slips.print');


     Route::get('/products/search', [PurchaseSlipController::class, 'productSearch'])
        ->name('products.search');


    Route::resource('products', ProductController::class);
    Route::resource('slips', PurchaseSlipController::class);



   

    Route::resource('purchases', PurchaseController::class);
});


// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
