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
        Schema::create('todo_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('todo_id')->constrained('todos')->onDelete('cascade');
            $table->string('document', 100);
            $table->timestamps();

            $table->unique(['todo_id', 'document']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('todo_documents');
    }
};
