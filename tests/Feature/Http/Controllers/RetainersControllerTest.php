<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Enums\DataCenter;
use App\Models\Enums\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RetainersControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    public function it_should_get_a_list_of_retainers(): void
    {
        // Arrange
        $user = User::factory()->create();
        $retainers = $user->retainers()->createMany([
            [
                'name' => 'Retainer 1',
                'server' => Server::GOBLIN,
                'data_center' => DataCenter::CRYSTAL,
            ],
            [
                'name' => 'Retainer 2',
                'server' => Server::GOBLIN,
                'data_center' => DataCenter::CRYSTAL,
            ],
        ]);
        $user->retainers()->saveMany($retainers);

        // Act
        $response = $this->actingAs($user)->get(route('retainers.list'));

        // Assert
        $response->assertJsonCount(2);
        $response->assertJson([
            ['retainer_name' => 'Retainer 1'],
            ['retainer_name' => 'Retainer 2'],
        ]);
    }

    #[Test]
    public function it_should_save_a_retainer(): void
    {
        // Arrange
        $user = User::factory()->create();
        $retainer = [
            'name' => 'Retainer 1',
            'server' => 'Goblin',
        ];

        // Act
        $response = $this->actingAs($user)->post(route('retainers.store'), $retainer);

        // Assert
        $response->assertCreated();
        $this->assertDatabaseHas('retainers', $retainer);
    }

    #[Test]
    public function it_should_not_save_the_retainer_because_the_user_already_has_10_retainers(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user->retainers()->createMany([
            ['name' => 'Retainer 1', 'server' => Server::GOBLIN, 'data_center' => DataCenter::CRYSTAL],
            ['name' => 'Retainer 2', 'server' => Server::GOBLIN, 'data_center' => DataCenter::CRYSTAL],
            ['name' => 'Retainer 3', 'server' => Server::GOBLIN, 'data_center' => DataCenter::CRYSTAL],
            ['name' => 'Retainer 4', 'server' => Server::GOBLIN, 'data_center' => DataCenter::CRYSTAL],
            ['name' => 'Retainer 5', 'server' => Server::GOBLIN, 'data_center' => DataCenter::CRYSTAL],
            ['name' => 'Retainer 6', 'server' => Server::GOBLIN, 'data_center' => DataCenter::CRYSTAL],
            ['name' => 'Retainer 7', 'server' => Server::GOBLIN, 'data_center' => DataCenter::CRYSTAL],
            ['name' => 'Retainer 8', 'server' => Server::GOBLIN, 'data_center' => DataCenter::CRYSTAL],
            ['name' => 'Retainer 9', 'server' => Server::GOBLIN, 'data_center' => DataCenter::CRYSTAL],
            ['name' => 'Retainer 10', 'server' => Server::GOBLIN, 'data_center' => DataCenter::CRYSTAL],
        ]);
        $retainer = ['name' => 'Retainer 11', 'server' => 'Goblin'];

        // Act
        $response = $this->actingAs($user)->post(route('retainers.store'), $retainer);

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name' => 'You can only have 10 retainers']);
    }

    #[Test]
    public function it_should_not_save_the_retainer_because_the_user_already_has_a_retainer_with_that_name(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user->retainers()->create([
            'name' => 'Retainer 1',
            'server' => Server::GOBLIN,
            'data_center' => DataCenter::CRYSTAL,
        ]);
        $retainer = ['name' => 'Retainer 1', 'server' => 'Goblin'];

        // Act
        $response = $this->actingAs($user)->post(route('retainers.store'), $retainer);

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name' => 'You already have a retainer with that name']);
    }

    #[Test]
    public function it_should_delete_the_retainer(): void
    {
        // Arrange
        $user = User::factory()->create();
        $retainer = $user->retainers()->create([
            'name' => 'Retainer 1',
            'server' => Server::GOBLIN,
            'data_center' => DataCenter::CRYSTAL,
        ]);

        // Act
        $response = $this->actingAs($user)->delete(route('retainers.destroy', ['retainerID' => $retainer->id]));

        // Assert
        $response->assertNoContent();
        $this->assertDatabaseMissing('retainers', ['id' => $retainer->id]);
    }
}
