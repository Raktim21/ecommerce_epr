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
        Schema::create('client_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('restrict');
            $table->string('invoice_no', 50)->unique();
            $table->foreignId('payment_type_id')->constrained('payment_types')->onDelete('restrict');
            $table->string('transaction_id', 100)->unique();
            $table->float('amount');
            $table->dateTime('occurred_on');
            $table->string('remarks', 500);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_transactions');
    }
};
