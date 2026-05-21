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
        Schema::create('saving_goal_deposits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('saving_goal_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('financial_account_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('amount', 15, 2);
            $table->date('deposit_date');
            $table->text('description')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'deposit_date']);
            $table->index(['saving_goal_id', 'deposit_date']);
            $table->index(['financial_account_id', 'deposit_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saving_goal_deposits');
    }
};
