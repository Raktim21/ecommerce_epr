<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('payment_types')->insert([
            ['name' => 'Cash','created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bank','created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bkash','created_at' => now(), 'updated_at' => now()],
            ['name' => 'Nagad','created_at' => now(), 'updated_at' => now()]
        ]);
    }
}
