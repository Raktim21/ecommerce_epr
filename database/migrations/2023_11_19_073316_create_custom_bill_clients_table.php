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
        Schema::create('custom_bill_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_bill_id')->constrained('custom_bills')->onDelete('cascade');
            $table->string('name', 100);
            $table->string('email', 100);
            $table->string('phone', 20);
            $table->string('company', 100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_bill_clients');
    }
};
