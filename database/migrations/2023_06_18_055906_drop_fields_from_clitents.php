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
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'status_id')) {
                $table->dropForeign(['status_id']);
                $table->dropIndex('clients_status_id_foreign');
                $table->dropColumn('status_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'status_id')) {
                $table->foreignId('status_id')->index()->nullable()
                    ->constrained('interest_statuses')->onDelete('restrict');
            }
        });
    }
};
