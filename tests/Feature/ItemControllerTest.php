<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;

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
                    'title',
                    'description',
                    'price',
                    'category',
                    'location',
                    'state',
                    'condition',
                    'publishDate',
                    'images',
                    'userId',
                    'created_at',
                    'updated_at',
                ],
            ]);
        // list can't contain sold items
        $response->assertJsonMissing(['state' => 'sold']);
    }

    // *** store item tests *** /
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_an_item()
    {
        $user = UserFactory::new()->create();

        $itemData = [
            'title' => $this->faker->name(),
            'description' => $this->faker->sentence(rand(4, 10)),
            'category' => $this->faker->randomElement(['Cascos', 'Monos', 'Guantes', 'Chaquetas', 'Pantalones', 'Botas', 'Accesorios', 'Ropa Interior', 'Recambios']),
            'price' => $this->faker->randomFloat(2, 0, 1000),
            'state' => $this->faker->randomElement(['available', 'reserved']),
            'condition' => $this->faker->numberBetween(0, 2),
            'publishDate' => $this->faker->date(),
            'userId' => $user->id,
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
                    'title',
                    'description',
                    'price',
                    'category',
                    'state',
                    'condition',
                    'publishDate',
                    'userId',
                    'created_at',
                    'updated_at',
                ],
            ]);
        $response->assertJsonMissing(['state' => 'sold']);
    }

    // *** update item tests *** /
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_updates_an_item()
    {
        $user = UserFactory::new()->create();
        $item = Item::factory()->create(['userId' => $user->id]);

        $item->title = 'updated title';
        $item->description = 'updated description';
        $itemData = $item->toArray();

        // Act
        $response = $this->putJson("/api/items/{$item->id}", $itemData);

        // Assert
        $response->assertStatus(200);

        $this->assertDatabaseHas('items', $itemData);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_error_trying_to_update_sold_item()
    {
        $user = UserFactory::new()->create();
        $item = Item::factory()->create(['userId' => $user->id, 'state' => 'sold']);

        $item->title = 'updated title';
        $item->description = 'updated description';
        $itemData = $item->toArray();

        // Act
        $response = $this->putJson("/api/items/{$item->id}", $itemData);

        // Assert
        $response->assertStatus(400);
        $response->assertJson(['error' => 'Sold items cannot be updated']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_validation_error_when_invalid_data()
    {
        // Arrange
        $invalidData = [
            'title' => 'John',
            'description' => 'description',
            'price' => 'invalidprice',
            'state' => 'invalidstate',
            'condition' => 33,
            'publishDate' => 'invalidpublishDate',
            'userId' => 'invaliduserId',
        ];

        // Act
        $response = $this->postJson('/api/items', $invalidData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'price',
                'category',
                'state',
                'condition',
                'publishDate',
                'userId',
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_error_when_item_not_found()
    {
        $user = UserFactory::new()->create();

        // Arrange
        $item = Item::factory()->create(['userId' => $user->id]);

        $item->id = (string) Str::uuid();
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
        Item::factory(10)->create(['userId' => $user->id, 'state' => 'available', 'condition' => 1]);

        $response = $this->postJson("/api/items/search", [
            'filters' => [
                ['column' => 'state', 'value' => 'available'],
                ['orderBy' => 'publishDate', 'order' => 'asc'],
                ['orderBy' => 'title', 'order' => 'desc'],
                ['column' => 'price', 'min' => 10, 'max' => 100],
                ['column' => 'condition', 'value' => 1],
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
                'title',
                'description',
                'price',
                'category',
                'state',
                'condition',
                'publishDate',
                'userId',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_error_when_search_with_invalid_filters()
    {
        $user = User::factory()->create();
        Item::factory(5)->create(['userId' => $user->id]);

        $response = $this->postJson("/api/items/search", [
            'filters' => [
                ['column' => 'state', 'value' => 'invalid_state'], // Invalid value for 'state'
                ['column' => 'title', 'value' => 'posts'],
                ['column' => 'created_at', 'value' => '2023-01-01'], // Invalid column
                ['orderBy' => 'price', 'order' => 'asc'],
                ['column' => 'condition', 'value' => 5], // Invalid value
            ]
        ]);

        $response->assertStatus(400);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_search_items_by_title()
    {
        $user = User::factory()->create();
        Item::factory()->create(['userId' => $user->id, 'title' => 'Item 1']);
        Item::factory()->create(['userId' => $user->id, 'title' => 'Item 2']);
        Item::factory()->create(['userId' => $user->id, 'title' => 'Item 3']);

        $response = $this->postJson("/api/items/search", [
            'filters' => [
                ['column' => 'title', 'value' => 'Item 1'],
            ]
        ]);

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['title' => 'Item 1']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_search_items_by_partial_title()
    {
        $user = User::factory()->create();
        Item::factory()->create(['userId' => $user->id, 'title' => 'Item 1']);
        Item::factory()->create(['userId' => $user->id, 'title' => 'Item 2']);
        Item::factory()->create(['userId' => $user->id, 'title' => 'Item 3']);

        $response = $this->postJson("/api/items/search", [
            'filters' => [
                ['column' => 'title', 'value' => 'Item'],
            ]
        ]);

        $response->assertOk();
        $response->assertJsonCount(3);
    }
}
