<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InterestStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('interest_statuses')->insert([
            ['name' => 'In Progress','created_at' => now(), 'updated_at' => now()],
            ['name' => 'Approved','created_at' => now(), 'updated_at' => now()],
            ['name' => 'Declined','created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
