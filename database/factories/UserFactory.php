<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
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
            // 'name' => $this->faker->name(),
            'name' => $this->faker->firstName(),
            // 'lastName' => $this->faker->unique()->regexify('[a-zA-Z0-9]{4,20}'),
            'lastName' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->randomElement([
                $this->faker->numerify('6########'), // Format for mobile phones starting with '6'
                $this->faker->numerify('7########')  // Format for mobile phones starting with '7'
            ]),
            'showPhone' => $this->faker->boolean,
            'profileDescription' => $this->faker->sentence(rand(4, 10)),
            'city' => $this->faker->city,
            'state' => $this->faker->state,
            'country' => 'España',
            'address' => $this->faker->address,
            'zipCode' => $this->faker->numerify('#####'),
        ];
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
