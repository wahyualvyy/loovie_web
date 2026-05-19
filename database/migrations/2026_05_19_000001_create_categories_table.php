<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['income', 'expense']); // income atau expense
            $table->string('color')->default('#6366f1'); // hex color untuk category
            $table->string('icon')->default('tag'); // icon name (bisa dari lucide-vue)
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
