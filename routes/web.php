<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinancialAccountController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\NoteController;
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
});

require __DIR__.'/settings.php';
