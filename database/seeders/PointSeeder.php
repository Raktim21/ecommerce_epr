<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PointSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('points')->insert([
            ['type' => 'Add Client', 'point' => 100,'created_at' => now(), 'updated_at' => now()],
            ['type' => 'Confirm Client', 'point' => 200 , 'created_at' => now(), 'updated_at' => now()]
        ]);
    }
}
