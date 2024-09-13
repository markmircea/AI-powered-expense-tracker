<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\BankStatementController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\TeamController;
use Illuminate\Http\Request;

// Public Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return Inertia::render('SpreadSheetComponent');
    })->name('dashboard');

    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update']);
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy']);
    Route::post('/transactions/delete-multiple', [TransactionController::class, 'destroyMultiple']);

    Route::get('/upload-bank-statement', function () {
        return Inertia::render('BankStatementUpload');
    });

    Route::post('/api/upload-bank-statement', [BankStatementController::class, 'upload']);
    Route::get('/api/previous-uploads', [BankStatementController::class, 'getPreviousUploads']);
    Route::get('/api/download-upload/{id}', [BankStatementController::class, 'downloadUpload']);
    Route::delete('/api/delete-upload/{id}', [BankStatementController::class, 'deleteUpload']);

    Route::resource('teams', TeamController::class);
    Route::post('/teams/{team}/add-member', [TeamController::class, 'addMember'])->name('teams.add-member');
    Route::post('/teams/{team}/remove-member', [TeamController::class, 'removeMember'])->name('teams.remove-member');
    Route::post('/teams/{team}/leave', [TeamController::class, 'leave'])->name('teams.leave');

    // Explicitly define the DELETE route for teams
    Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');


    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/user/teams', [TeamController::class, 'getUserTeams']);
});

// Catch-all Route
Route::get('/{any}', function () {
    return redirect()->route('login');
})->where('any', '.*')->middleware('guest');
