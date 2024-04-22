<?php

namespace Tests\Feature;

use Database\Factories\ItemFactory;
use Database\Factories\TransactionFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_transactions_response_structure(): void
    {
        $response = $this->get('/api/transactions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'item_id',
                    'buyer_id',
                    'seller_id',
                    'price',
                    'transaction_date',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    // *** store transaction tests *** /
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_a_transaction()
    {
        $transactionData = TransactionFactory::new()->make()->toArray();

        // Act
        $response = $this->postJson('/api/transactions', $transactionData);

        // Assert
        $response->assertStatus(201)
            ->assertJson(['message' => 'Transaction created'])
            ->assertJsonStructure([
                'transaction' => [
                    'id',
                    'item_id',
                    'buyer_id',
                    'seller_id',
                    'price',
                    'transaction_date',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_an_error_creating_an_invalid_transaction()
    {
        // Act
        $response = $this->postJson('/api/transactions', []);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'item_id',
                'buyer_id',
                'seller_id',
                'price',
                'transaction_date',
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_an_error_creating_a_transaction_with_invalid_data()
    {
        $transactionData = [
            'item_id' => 'xxx',
            'buyer_id' => 'xxx',
            'seller_id' => 'xxx',
            'price' => 'xxx',
            'transaction_date' => 'xxx',
        ];

        // Act
        $response = $this->postJson('/api/transactions', $transactionData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'item_id',
                'buyer_id',
                'seller_id',
                'price',
                'transaction_date',
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_an_error_creating_a_transaction_with_an_already_sold_item()
    {
        $transactionData = TransactionFactory::new()->make()->toArray();
        $item = ItemFactory::new()->create(['state' => 'sold', 'user_id' => $transactionData['seller_id']]);
        $transactionData['item_id'] = $item->id;

        // Act
        $response = $this->postJson('/api/transactions', $transactionData);

        // Assert
        $response->assertStatus(400)
            ->assertJson(['error' => 'Item is already sold']);
    }

    // *** show transaction tests *** /
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_shows_a_transaction()
    {
        $transaction = TransactionFactory::new()->create();

        // Act
        $response = $this->get("/api/transactions/{$transaction->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJson($transaction->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_an_error_when_showing_a_non_existent_transaction()
    {
        // Act
        $response = $this->get('/api/transactions/999');

        // Assert
        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Transaction not found',
            ]);
    }

    // *** update transaction test *** /
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_error_trying_to_update_a_transaction()
    {
        $transaction = TransactionFactory::new()->create();
        $transactionData = $transaction->toArray();

        // Act
        $response = $this->putJson("/api/transactions/{$transaction->id}", $transactionData);

        // Assert
        $response->assertStatus(405)
            ->assertJson(['error' => 'Method not allowed']);
    }

    // *** delete transaction tests *** /
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_an_error_deleting_a_transaction()
    {
        // Act
        $response = $this->delete('/api/transactions/999');

        // Assert
        $response->assertStatus(405)
            ->assertJson([
                'error' => 'Method not allowed',
            ]);
    }
}
