<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('financial_account_id')->constrained('financial_accounts')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->enum('type', ['income', 'expense']); // Tipe transaksi
            $table->decimal('amount', 15, 2); // Nominal
            $table->date('transaction_date'); // Tanggal transaksi
            $table->text('description')->nullable(); // Deskripsi / keterangan
            $table->string('attachment')->nullable(); // Path untuk bukti/attachment
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('financial_account_id');
            $table->index('category_id');
            $table->index('transaction_date');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
