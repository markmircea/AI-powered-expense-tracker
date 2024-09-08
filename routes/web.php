<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\BankStatementController;


Route::get('/', function () {
    return Inertia::render('SpreadSheetComponent');
});

Route::get('/transactions', [TransactionController::class, 'index']);
Route::post('/transactions', [TransactionController::class, 'store']);
Route::put('/transactions/{transaction}', [TransactionController::class, 'update']);
Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy']);

Route::get('/upload-bank-statement', function () {
    return Inertia::render('BankStatementUpload');
});

Route::post('/api/upload-bank-statement', [BankStatementController::class, 'upload']);

