<?php

use App\Http\Controllers\FinancialAccountController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    
    // Financial Accounts routes
    Route::resource('financial-accounts', FinancialAccountController::class);
});

require __DIR__.'/settings.php';
