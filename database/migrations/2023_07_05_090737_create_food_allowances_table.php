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
        Schema::create('food_allowances', function (Blueprint $table) {
            $table->id();
            $table->string('lat', 30);
            $table->string('lng', 30);
            $table->string('address', 500)->nullable();
            $table->float('amount')->default(0.00);
            $table->string('note', 500)->nullable();
            $table->string('document', 100)->nullable();
            $table->dateTime('occurred_on');
            $table->foreignId('created_by')->index()->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('client_id')->index()->nullable()->constrained('clients')->onDelete('set null');
            $table->foreignId('follow_up_id')->index()->nullable()->constrained('follow_up_infos')->onDelete('set null');
            $table->tinyInteger('allowance_status')->default(0)->comment('0 = pending, 1 = paid, 2 = rejected');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_allowances');
    }
};
