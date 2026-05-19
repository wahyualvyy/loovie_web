<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(User::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignIdFor(Category::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->string('month', 7); // format: YYYY-MM
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'category_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
