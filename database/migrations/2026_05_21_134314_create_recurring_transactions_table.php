<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recurring_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('financial_account_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('category_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title');
            $table->enum('type', ['income', 'expense']);
            $table->decimal('amount', 15, 2);

            $table->enum('frequency', [
                'daily',
                'weekly',
                'monthly',
                'yearly',
            ]);

            $table->date('start_date');
            $table->date('next_date');
            $table->date('end_date')->nullable();

            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamp('last_generated_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index(['user_id', 'next_date']);
            $table->index(['financial_account_id']);
            $table->index(['category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_transactions');
    }
};
