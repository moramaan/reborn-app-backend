<?php

namespace Tests\Feature;

use App\Models\Item;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class ItemControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_items_response_structure(): void
    {
        $response = $this->get('/api/items');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'state',
                    'publish_date',
                    'user_id',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    // *** store item tests *** /
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_an_item()
    {
        $user = UserFactory::new()->create();

        $itemData = [
            'name' => $this->faker->name(),
            'description' => $this->faker->sentence(rand(4, 10)),
            'price' => $this->faker->randomFloat(2, 0, 1000),
            'state' => $this->faker->randomElement(['available', 'sold', 'reserved']),
            'publish_date' => $this->faker->date(),
            'user_id' => $user->id,
        ];

        // Act
        $response = $this->postJson('/api/items', $itemData);
        if ($response->getStatusCode() !== 201) {
            dump($response->getContent());
        }

        // Assert
        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Item created',
            ])
            ->assertJsonStructure([
                'item' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'state',
                    'publish_date',
                    'user_id',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    // *** update item tests *** /
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_updates_an_item()
    {
        $user = UserFactory::new()->create();
        $item = Item::factory()->create(['user_id' => $user->id]);

        $item->name = 'updated name';
        $item->description = 'updated description';
        $itemData = $item->toArray();

        // Act
        $response = $this->putJson("/api/items/{$item->id}", $itemData);
        if (
            $response->getStatusCode() !==
            200
        ) {
            dump($response->getContent());
        }

        // Assert
        $response->assertStatus(200);
        // ->assertJson($itemData);

        $this->assertDatabaseHas('items', $itemData);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_validation_error_when_invalid_data()
    {
        // Arrange
        $invalidData = [
            'name' => 'John',
            'description' => 'description',
            'price' => 'invalidprice',
            'state' => 'invalidstate',
            'publish_date' => 'invalidpublish_date',
            'user_id' => 'invaliduser_id',
        ];

        // Act
        $response = $this->postJson('/api/items', $invalidData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'price',
                'state',
                'publish_date',
                'user_id',
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_error_when_item_not_found()
    {
        $user = UserFactory::new()->create();
        
        // Arrange
        $item = Item::factory()->create(['user_id' => $user->id]);

        $item->id = 33;
        $itemData = $item->toArray();

        // Act
        $response = $this->putJson("/api/items/{$item->id}", $itemData);

        // Assert
        $response->assertStatus(404);
    }
}
