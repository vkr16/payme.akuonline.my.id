<?php

use App\Http\Controllers\BillController;
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
