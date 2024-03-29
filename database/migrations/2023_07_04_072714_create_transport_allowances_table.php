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
            $table->string('from_lat', 30);
            $table->string('from_lng', 30);
            $table->string('from_address', 500)->nullable();
            $table->dateTime('start_time');
            $table->string('to_lat', 30)->nullable();
            $table->string('to_lng', 30)->nullable();
            $table->string('to_address', 500)->nullable();
            $table->dateTime('end_time')->nullable();
            $table->string('transport_type', 20)->nullable();
            $table->float('amount')->default(0.00);
            $table->string('document', 100)->nullable();
            $table->string('note', 500)->nullable();
            $table->string('visit_type', 20)->nullable();
            $table->foreignId('created_by')->index()->nullable()->constrained('users')->onDelete('restrict');
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
        Schema::dropIfExists('transport_allowances');
    }
};
