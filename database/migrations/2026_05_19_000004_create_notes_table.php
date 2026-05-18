<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->longText('content');
            $table->date('note_date');
            $table->string('label')->nullable(); // Kategori/label catatan
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('note_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
