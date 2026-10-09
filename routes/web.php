<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\StockOutController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureActiveUser;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('login.attempt');
});
Route::middleware(['auth', EnsureActiveUser::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::resource('barang', ProductController::class)->parameters(['barang'=>'product'])->except(['show','destroy'])->names('products');
    Route::resource('pbf', SupplierController::class)->parameters(['pbf'=>'supplier'])->except(['show','destroy'])->names('suppliers');
    Route::get('/barang-masuk', [PurchaseController::class,'index'])->name('purchases.index');
    Route::get('/barang-masuk/tambah', [PurchaseController::class,'create'])->name('purchases.create');
    Route::post('/barang-masuk/draft', [PurchaseController::class,'saveDraft'])->name('purchases.draft.store');
    Route::get('/barang-masuk/draft/{draft}/edit', [PurchaseController::class,'editDraft'])->name('purchases.draft.edit');
    Route::delete('/barang-masuk/draft/{draft}', [PurchaseController::class,'deleteDraft'])->name('purchases.draft.delete');
    Route::post('/barang-masuk', [PurchaseController::class,'store'])->name('purchases.store');
    Route::get('/barang-masuk/{invoice}', [PurchaseController::class,'show'])->name('purchases.show');
    Route::get('/barang-masuk/{invoice}/cetak', [PurchaseController::class,'print'])->name('purchases.print');
    Route::get('/barang-masuk/{invoice}/lampiran', [PurchaseController::class,'attachment'])->name('purchases.attachment');
    Route::get('/barang-keluar', [StockOutController::class,'index'])->name('stock-outs.index');
    Route::get('/barang-keluar/tambah', [StockOutController::class,'create'])->name('stock-outs.create');
    Route::post('/barang-keluar', [StockOutController::class,'store'])->name('stock-outs.store');
    Route::get('/stok', [StockController::class,'index'])->name('stock.index');
    Route::get('/stok/{product}', [StockController::class,'show'])->name('stock.show');
    Route::get('/tagihan', [PaymentController::class,'index'])->name('payments.index');
    Route::get('/laporan', [ReportController::class,'index'])->name('reports.index');
    Route::get('/laporan/export', [ReportController::class,'export'])->name('reports.export');
    Route::middleware('can:admin')->group(function () {
        Route::post('/barang-masuk/{invoice}/lampiran', [PurchaseController::class,'uploadAttachment'])->name('purchases.upload-attachment');
        Route::post('/tagihan/{invoice}/bayar', [PaymentController::class,'store'])->name('payments.store');
        Route::post('/stok/batch/{batch}/koreksi', [StockController::class,'adjust'])->name('stock.adjust');
        Route::resource('pengguna', UserController::class)->parameters(['pengguna'=>'user'])->except(['show','destroy'])->names('users');
    });
});
