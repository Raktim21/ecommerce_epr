<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TodoStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('todo_statuses')->insert([
            ['id' => 1, 'name' => 'Todo', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'In Progress', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'In Review', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'Complete', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'name' => 'Cancelled', 'created_at' => now(), 'updated_at' => now()]
        ]);
    }
}
