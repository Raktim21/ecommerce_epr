<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Clients>
 */
class ClientsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company' => fake()->company(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone_no' => fake()->phoneNumber(),
            'area' => fake()->address(),
            'status_id' => 2,
            'added_by' => 1
        ];
    }
}
