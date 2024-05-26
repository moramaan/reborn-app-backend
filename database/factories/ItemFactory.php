<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
            'id' => (string) Str::uuid(),
            'title' => $this->faker->name(),
            'description' => $this->faker->sentence(rand(4, 10)),
            'price' => $this->faker->randomFloat(2, 0, 1000),
            'category' => $this->faker->randomElement(['Cascos', 'Monos', 'Guantes', 'Chaquetas', 'Pantalones', 'Botas', 'Accesorios', 'Ropa Interior', 'Recambios']),
            'state' => $this->faker->randomElement(['available', 'reserved']),
            'condition' => $this->faker->numberBetween(0, 2),
            'publishDate' => $this->faker->date(),
            'userId' => $this->faker->numberBetween(1, null)
        ];
    }
}
