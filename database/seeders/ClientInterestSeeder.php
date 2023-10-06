<?php

namespace Database\Seeders;

use App\Models\Clients;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientInterestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = Clients::whereNull('confirmation_date')
            ->orderByDesc('id')->limit(300)->get();

        foreach ($clients as $client)
        {
            $random = rand(5,9).'0';

            $client->update([
                'interest_status' => $random
            ]);
        }
    }
}
