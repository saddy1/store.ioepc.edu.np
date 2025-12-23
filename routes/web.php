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
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeAuthController;
use App\Http\Controllers\StoreOutController;
use App\Http\Controllers\EmployeeStoreOutController;

// Purchase Slips


use GuzzleHttp\Middleware;

Route::get('/', [DashboardController::class, 'home'])->name('home');



// Admin auth
Route::get('/admin', [AuthController::class, 'showAdminLogin'])->name('admin.login.form');
Route::post('/login/admin', [AuthController::class, 'adminLogin'])->name('admin.login');











Route::group(['middleware' => 'admin.auth'], function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');

    Route::resource('suppliers', SupplierController::class);
    Route::resource('categories', ItemCategoryController::class);
    Route::resource('brands', BrandController::class);
    Route::resource('department', DepartmentController::class);



    Route::resource('product_categories', CategoryController::class);
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


    Route::get('/employees/search', [EmployeeController::class, 'search'])->name('employees.search');

    Route::resource('employees', EmployeeController::class);


    Route::get('/employee/store-out', [EmployeeStoreOutController::class, 'index'])
        ->name('employee.store_out.index');

    Route::prefix('store')->name('store.')->group(function () {
          Route::get('/entry-items/search', [StoreOutController::class, 'searchEntryItems'])
        ->name('entry-items.search');

        Route::get('/out',          [StoreOutController::class, 'index'])->name('out.index');
        Route::get('/out/create',   [StoreOutController::class, 'create'])->name('out.create');
        Route::post('/out',         [StoreOutController::class, 'store'])->name('out.store');
        Route::get('/out/{storeOut}', [StoreOutController::class, 'show'])->name('out.show');
        Route::post('/out/items/{storeOutItem}/return', [StoreOutController::class, 'markReturned'])
    ->name('out.items.return');
    });
});


// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
















//EMployee Routes Begin
Route::get('/employee/login', [EmployeeAuthController::class, 'showLogin'])->name('employee.login');
Route::post('/employee/login', [EmployeeAuthController::class, 'login'])->name('employee.login.post');
Route::post('/employee/logout', [EmployeeAuthController::class, 'logout'])->name('employee.logout');
Route::group(['middleware' => ['auth:employee', 'employee.mustChange']], function () {
    // Simple dashboard placeholder
    Route::get('/employee/dashboard', function () {
        return view('Frontend.employee.dashboard');
    })->name('employee.dashboard');

    // Force change-password routes
    Route::get('/employee/change-password', [EmployeeAuthController::class, 'showChangePassword'])
        ->name('employee.password.show'); // note: this is whitelisted in middleware
    Route::post('/employee/change-password', [EmployeeAuthController::class, 'updatePassword'])
        ->name('employee.password.update');
});
//Employee Routes End