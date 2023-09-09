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
        Schema::create('kpi_look_ups', function (Blueprint $table) {
            $table->id();
            $table->string('category', 50)->unique();
            $table->integer('client_count', false, true);
            $table->float('amount');
            $table->float('per_client_amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_look_ups');
    }
};
