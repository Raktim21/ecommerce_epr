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
        Schema::create('follow_up_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->index()->constrained('clients')->onDelete('cascade');
            $table->dateTime('followup_session');
            $table->string('notes', 500)->nullable();
            $table->foreignId('added_by')->index()->constrained('users')->onDelete('restrict');
            $table->timestamps();

            $table->unique(['client_id', 'followup_session']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follow_up_reminders');
    }
};
