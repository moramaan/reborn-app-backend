<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $buyer = UserFactory::new()->create();
        $seller = UserFactory::new()->create();
        $item = ItemFactory::new()->create(['userId' => $seller->id]);
        return [
            'id' => (string) Str::uuid(),
            'item_id' => $item->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'price' => $this->faker->randomFloat(2, 0, 1000),
            'transaction_date' => $this->faker->date(),
        ];
    }
}
