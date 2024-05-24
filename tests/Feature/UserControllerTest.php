<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_users_response_structure(): void
    {
        UserFactory::new()->count(5)->create();

        $response = $this->get('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'lastName',
                    'email',
                    'phone',
                    'showPhone',
                    'profileDescription',
                    'city',
                    'state',
                    'country',
                    'address',
                    'zipCode',
                    'isAdmin',
                    'isDeleted',
                    'created_at',
                    'updated_at',
                ],
            ]);
        //assert that only active users are returned, isDeleted = false
        $response->assertJsonMissing(['isDeleted' => true]);

    }

    // *** store user tests *** /
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_a_user()
    {
        // Arrange
        $userData = [
            'name' => $this->faker->name,
            'lastName' => $this->faker->unique()->regexify('[a-zA-Z0-9]{4,20}'),
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'showPhone' => $this->faker->boolean,
            'profileDescription' => $this->faker->sentence(rand(4, 10)),
            'city' => $this->faker->city,
            'state' => $this->faker->state,
            'country' => $this->faker->country,
            'address' => $this->faker->address,
            'zipCode' => $this->faker->numberBetween(10000, 99999),
        ];

        // Act
        $response = $this->postJson('/api/users', $userData);

        // Assert
        $response->assertStatus(201)
            ->assertJson(['message' => 'User created'])
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'lastName',
                    'email',
                    'phone',
                    'showPhone',
                    'profileDescription',
                    'city',
                    'state',
                    'country',
                    'address',
                    'zipCode',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('users', $userData);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_validation_error_when_invalid_data_is_sent()
    {
        // Arrange
        $invalidData = [
            'name' => 'John',
            'lastName' => 'Doe',
            'email' => 'invalidemail',
        ];

        // Act
        $response = $this->postJson('/api/users', $invalidData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    // *** update user tests *** /
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_updates_an_existing_user()
    {
        // Create a user to update
        $user = UserFactory::new()->create();

        $user->name = 'Updated Name';

        // New data for updating the user
        $updatedData = $user->toArray();

        // Act: Send a PUT request to update the user
        $response = $this->putJson('/api/users/' . $user->id, $updatedData);

        // Assert: Check if the response indicates success (HTTP 200) and contains the updated user data
        $response->assertStatus(200)
            ->assertJson($updatedData);

        // Optionally, you can assert that the user's data has been updated in the database
        $this->assertDatabaseHas('users', $updatedData);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_error_when_user_not_found()
    {
        // Act: Send a PUT request to update a non-existent user
        $response = $this->putJson('/api/users/9999', []);

        // Assert: Check if the response indicates that the user was not found (HTTP 404)
        $response->assertStatus(404)
            ->assertJson(['error' => 'User not found']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_validation_error_on_invalid_data()
    {
        // Create a user to update
        $user = User::factory()->create();
        $user->save();
        $user->name = null;
        $user->email = 'invalidemail';

        $invalidData = $user->toArray();

        // Act: Send a PUT request to update the user with invalid data
        $response = $this->putJson('/api/users/' . $user->id, $invalidData);

        // Assert: Check if the response indicates validation failure (HTTP 422) and contains validation errors
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_deletes_an_existing_user()
    {
        $user = User::factory()->create();

        $response = $this->deleteJson('/api/users/' . $user->id);

        $response->assertStatus(200);

        //assert that user is flagged as deleted in the database
        $this->assertDatabaseHas('users', ['id' => $user->id, 'isDeleted' => true]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_deletes_users_unsold_items_when_user_is_deleted()
    {
        $user = User::factory()->hasItems(3)->create();

        $response = $this->deleteJson('/api/users/' . $user->id);

        $response->assertStatus(200);

        //assert that user is flagged as deleted in the database
        $this->assertDatabaseHas('users', ['id' => $user->id, 'isDeleted' => true]);
        //assert that user's unsold items are deleted
        $this->assertCount(0, $user->unsoldItems()->get());
    }
}
