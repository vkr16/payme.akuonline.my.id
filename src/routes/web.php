<?php

use App\Http\Controllers\BillController;
use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

// Redirect root to /create using clean relative path
Route::get('/', function () {
    return redirect('/create');
});

Route::get('/create', [BillController::class, 'create'])->name('bills.create');
Route::post('/bills/parse-receipt', [BillController::class, 'parseReceipt'])->name('bills.parse-receipt');
Route::post('/bills', [BillController::class, 'store'])->name('bills.store');
Route::get('/b/{slug}', [BillController::class, 'show'])->name('bills.show');
Route::get('/b/{slug}/receipt', [BillController::class, 'receiptImage'])->name('bills.receipt');
Route::post('/b/{slug}/qris', [BillController::class, 'generateDynamicQris'])->name('bills.qris');
Route::post('/b/{slug}/claim', [BillController::class, 'claimPayment'])->name('bills.claim');
Route::get('/clean-retention', [BillController::class, 'cleanRetention'])->name('bills.clean-retention');

Route::get('/instant-qris', function () {
    return redirect('/create?instant=1');
})->name('qris.instant');

Route::get('/qris-instant', function () {
    return redirect('/create?instant=1');
});

// Invoice Maker routes
Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
Route::get('/i/{slug}', [InvoiceController::class, 'show'])->name('invoices.show');
Route::get('/i/{slug}/logo', [InvoiceController::class, 'logoImage'])->name('invoices.logo');
Route::get('/i/{slug}/qris-image', [InvoiceController::class, 'qrisImage'])->name('invoices.qris-image');
Route::post('/i/{slug}/status', [InvoiceController::class, 'updateStatus'])->name('invoices.status');


