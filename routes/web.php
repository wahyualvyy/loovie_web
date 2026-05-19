<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinancialAccountController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\TransactionExportController;
use App\Http\Controllers\NoteExportController;
use App\Http\Controllers\AccountExportController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Financial Accounts routes
    Route::resource('financial-accounts', FinancialAccountController::class);

    // Categories routes
    Route::resource('categories', CategoryController::class)->except('show');

    // Transactions routes
    Route::resource('transactions', TransactionController::class)->except('show');

    // Notes routes
    Route::resource('notes', NoteController::class)->except('show');

    // Export routes for Transactions
    Route::get('transactions/export/csv', [TransactionExportController::class, 'exportCSV'])->name('transactions.export.csv');
    Route::get('transactions/export/print', [TransactionExportController::class, 'printReport'])->name('transactions.export.print');

    // Export routes for Notes
    Route::get('notes/export/csv', [NoteExportController::class, 'exportCSV'])->name('notes.export.csv');
    Route::get('notes/export/print', [NoteExportController::class, 'printReport'])->name('notes.export.print');

    // Export routes for Accounts
    Route::get('financial-accounts/export/csv', [AccountExportController::class, 'exportCSV'])->name('accounts.export.csv');
    Route::get('financial-accounts/export/print', [AccountExportController::class, 'printReport'])->name('accounts.export.print');

    // User Management routes (Admin only)
    Route::get('/data-master/users', [UserManagementController::class, 'index'])
        ->name('users.index');

    Route::post('/data-master/users', [UserManagementController::class, 'store'])
        ->name('users.store');

    Route::put('/data-master/users/{user}', [UserManagementController::class, 'update'])
        ->name('users.update');

    Route::delete('/data-master/users/{user}', [UserManagementController::class, 'destroy'])
        ->name('users.destroy');
});

require __DIR__ . '/settings.php';

