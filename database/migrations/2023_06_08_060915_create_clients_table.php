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
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone_no')->unique();
            $table->string('area');
            $table->foreignId('status_id')->constrained('interest_statuses')->onDelete('restrict');
            $table->string('client_opinion')->nullable();
            $table->string('officer_opinion')->nullable();
            $table->string('document')->nullable();
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
