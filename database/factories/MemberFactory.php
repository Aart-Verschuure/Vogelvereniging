<?php

namespace Database\Factories;

use App\Models\Adress;
use App\Models\Member;
use App\Models\MemberType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'date_of_birth' => fake()->date(),
            'nbvv_number' => fake()->unique()->regexify('[0-9]{6}'),
            'is_active' => true,
            'registration_date' => fake()->dateTimeThisYear(),
            'status' => Member::STATUS_ACTIVE,
            'approved_at' => now(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'address_id' => Adress::factory(),
            'member_type_id' => fn () => MemberType::inRandomOrder()->value('id') ?? MemberType::factory(),
        ];
    }

    /**
     * Een aanmelding die nog in quarantaine staat.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Member::STATUS_PENDING,
            'is_active' => 0,
            'approved_at' => null,
            'registration_date' => today(),
            'signature_name' => $attributes['first_name'].' '.$attributes['last_name'],
            'signed_at' => now(),
            'signature_ip' => '127.0.0.1',
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
