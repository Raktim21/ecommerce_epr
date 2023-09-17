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
        Schema::create('todos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('added_by')->constrained('users')->onDelete('restrict');
            $table->string('title', 100);
            $table->string('detail', 500);
            $table->tinyInteger('priority_level', false, true)->comment('1:High Priority, 2:Important, 3:Neutral');
            $table->dateTime('deadline');
            $table->foreignId('status_id')->default(1)->constrained('todo_statuses')->onDelete('restrict');
            $table->timestamps();

            $table->unique(['added_by','title']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('todos');
    }
};
