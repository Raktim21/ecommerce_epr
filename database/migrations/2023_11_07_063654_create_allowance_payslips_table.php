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
        Schema::create('allowance_payslips', function (Blueprint $table) {
            $table->id();
            $table->string('payslip_uuid', 30)->unique();
            $table->string('url', 100);
            $table->tinyInteger('allowance_type', false, true)->default(1)->comment('1: transport, 2: food');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allowance_payslips');
    }
};
