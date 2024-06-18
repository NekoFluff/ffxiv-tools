<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Enums\DataCenter;
use App\Models\Enums\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RetainersControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    public function it_should_get_a_list_of_retainers()
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
}
