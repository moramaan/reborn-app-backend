<?php

namespace Tests\Feature;

use App\Models\Item;
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
        $item = Item::factory()->create();
        $itemData = $item->toArray();

        // Act
        $response = $this->postJson('/api/items', $itemData);
        if ($response->getStatusCode() !== 201) {
            dump($response->getContent());
        }

        // Assert
        $response->assertStatus(201)
            ->assertJson($itemData);
    }

    // *** update item tests *** /
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_updates_an_item()
    {
        $item = Item::factory()->create();
        $item->save();

        $item->name = 'updated name';

        $itemData = $item->toArray();

        // Act
        $response = $this->putJson("/api/items/{$item->id}", $itemData);
        if ($response->getStatusCode() !==
            200) {
            dump($response->getContent());
        }
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
        // Arrange
        $item = Item::factory()->create();
        $item->save();

        $item->id = 33;

        $itemData = $item->toArray();

        // Act
        $response = $this->putJson("/api/items/{$item->id}", $itemData);

        // Assert
        $response->assertStatus(404);
    }
}
