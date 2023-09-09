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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('company');
            $table->string('name', 150);
            $table->string('email', 150)->default('N/A');
            $table->string('phone_no', 20);
            $table->string('area', 255);
            $table->string('latitude', 30)->nullable();
            $table->string('longitude', 30)->nullable();
            $table->string('product_type', 255);
            $table->tinyInteger('interest_status', false, true)->default(0);
            $table->string('client_opinion')->nullable();
            $table->string('officer_opinion')->nullable();
            $table->string('document', 100)->nullable();
            $table->foreignId('added_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('confirmation_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
