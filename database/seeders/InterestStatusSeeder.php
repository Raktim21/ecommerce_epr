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
            ['name' => '0','created_at' => now(), 'updated_at' => now()],
            ['name' => '10','created_at' => now(), 'updated_at' => now()],
            ['name' => '20','created_at' => now(), 'updated_at' => now()],
            ['name' => '30','created_at' => now(), 'updated_at' => now()],
            ['name' => '40','created_at' => now(), 'updated_at' => now()],
            ['name' => '50','created_at' => now(), 'updated_at' => now()],
            ['name' => '60','created_at' => now(), 'updated_at' => now()],
            ['name' => '70','created_at' => now(), 'updated_at' => now()],
            ['name' => '80','created_at' => now(), 'updated_at' => now()],
            ['name' => '90','created_at' => now(), 'updated_at' => now()],
            ['name' => '100','created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
