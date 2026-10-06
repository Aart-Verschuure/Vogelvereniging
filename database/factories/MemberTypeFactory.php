<?php

namespace Database\Factories;

use App\Models\MemberType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemberType>
 */
class MemberTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'description' => fake()->sentence(),
        ];
    }

    /**
     * Elke lidsoort krijgt een prijs voor het huidige jaar.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (MemberType $memberType) {
            $memberType->prices()->create(['year' => now()->year, 'price' => fake()->numberBetween(10, 50)]);
        });
    }
}
