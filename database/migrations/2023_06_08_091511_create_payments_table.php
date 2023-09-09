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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('restrict');
            $table->foreignId('payment_type_id')->constrained('payment_types')->onDelete('restrict');
            $table->foreignId('payment_category_id')->constrained('payment_categories')->onDelete('restrict');
            $table->string('transaction_id', 50)->nullable();
            $table->string('invoice_no', 50)->unique();
            $table->double('amount',8,2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
