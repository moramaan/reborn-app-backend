<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class ItemControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    #[\PHPUnit\Framework\Attributes\Test]
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

    // *** Dynamic filtered search tests *** //
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_search_items_with_valid_filters()
    {
        $user = User::factory()->create();
        Item::factory(10)->create(['user_id' => $user->id, 'state' => 'available']);

        $response = $this->postJson("/api/items/search", [
            'filters' => [
                ['column' => 'state', 'value' => 'available'],
                ['orderBy' => 'publish_date', 'order' => 'asc'],
                ['orderBy' => 'name', 'order' => 'desc'],
                ['column' => 'price', 'min' => 10, 'max' => 100]
            ]
        ]);
        if ($response->status() !== 200) {
            dump($response->json());
        }
        $response->assertOk();
        $response->assertJsonCount(10);
        $response->assertJsonStructure([
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
        // $response->assertJsonFragment(['id' => $item1->id]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_search_items_with_invalid_filters()
    {
        $user = User::factory()->create();
        Item::factory(5)->create(['user_id' => $user->id]);

        $response = $this->postJson("/api/items/search", [
            'filters' => [
                ['column' => 'state', 'value' => 'invalid_state'], // Invalid value for 'state'
                ['column' => 'name', 'value' => 'posts'],
                ['column' => 'created_at', 'value' => '2023-01-01'], // Invalid column
                ['orderBy' => 'price', 'order' => 'asc']
            ]
        ]);

        $response->assertOk();
        $response->assertJsonCount(0); // No items should match the filters
    }
}
