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
        Schema::table('payment_categories', function (Blueprint $table) {

            $table->longText('description')->nullable()->after('name');
            $table->float('price')->after('description')->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_categories', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->dropColumn('price');
        });
    }
};
