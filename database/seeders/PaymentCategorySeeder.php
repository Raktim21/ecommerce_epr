<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('payment_categories')->insert([
            ['name' => 'Monthly Payment','created_at' => now(), 'updated_at' => now()],
            ['name' => 'Subscription','created_at' => now(), 'updated_at' => now()]
        ]);
    }
}
