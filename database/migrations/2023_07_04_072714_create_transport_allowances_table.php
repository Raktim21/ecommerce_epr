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
        Schema::create('transport_allowances', function (Blueprint $table) {
            $table->id();
            $table->longText('from_lat');
            $table->longText('from_lng');
            $table->longText('from_address')->nullable();
            $table->dateTime('start_time');
            $table->longText('to_lat')->nullable();
            $table->longText('to_lng')->nullable();
            $table->longText('to_address')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->string('transport_type')->nullable();
            $table->float('amount')->default(0.00);
            $table->longText('document')->nullable();
            $table->longText('note')->nullable();
            $table->longText('visit_type')->nullable();
            $table->foreignId('created_by')->index()->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('client_id')->index()->nullable()->constrained('clients')->onDelete('set null');
            $table->foreignId('follow_up_id')->index()->nullable()->constrained('follow_up_infos')->onDelete('set null');
            $table->integer('allowance_status')->default(0)->comment('0 = pending, 1 = confirmed, 2 = rejected, 3 = warning');
            $table->integer('travel_status')->default(0)->comment('0 = pending, 1 = confirmed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transport_allowances');
    }
};
