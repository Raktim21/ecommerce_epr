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
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('restrict');
            $table->year('year_name');
            $table->foreignId('month_id')->constrained('months')->onDelete('restrict');
            $table->float('payable_amount');
            $table->float('paid_amount');
            $table->float('incentive_paid')->default(0);
            $table->tinyInteger('admin_status')->default(0)->comment('0:pending,1:approved,2:declined');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};
