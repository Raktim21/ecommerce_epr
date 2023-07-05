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
            $table->string('lat');
            $table->string('lng');
            $table->text('address')->nullable();
            $table->float('amount')->default(0.00);
            $table->text('note')->nullable();
            $table->string('document')->nullable();
            $table->dateTime('occurred_on');
            $table->foreignId('created_by')->index()->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('client_id')->index()->nullable()->constrained('clients')->onDelete('set null');
            $table->foreignId('follow_up_id')->index()->nullable()->constrained('follow_up_infos')->onDelete('set null');
            $table->tinyInteger('allowance_status')->default(0)->comment('0 = pending, 1 = confirmed, 2 = rejected, 3 = warning');
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
