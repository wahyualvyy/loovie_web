<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name'); // Nama akun (Cash, Bank BCA, Dana, etc)
            $table->string('type'); // Tipe akun (cash, bank, digital_wallet, etc)
            $table->decimal('initial_balance', 15, 2)->default(0); // Saldo awal
            $table->decimal('current_balance', 15, 2)->default(0); // Saldo saat ini (dihitung dari transaksi)
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_accounts');
    }
};
