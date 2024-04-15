<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->sentence(rand(4, 10)),
            'price' => $this->faker->randomFloat(2, 0, 1000),
            'state' => $this->faker->randomElement(['available', 'sold', 'reserved']),
            'publish_date' => $this->faker->date(),
            'user_id' => $this->faker->numberBetween(1, null) //pendiente revisar si esto funciona solo con un parametro
        ];
    }
}
