<?php

namespace Tests\Feature\Livewire;

use App\Livewire\CreateRetainer;
use App\Models\Enums\DataCenter;
use App\Models\Enums\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateRetainerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_should_create_a_retainer(): void
    {
        // Arrange
        $user = User::factory()->create();
        $retainer = [
            'name' => 'Retainer 1',
            'server' => 'Goblin',
        ];

        // Act
        Livewire::actingAs($user)
            ->test(CreateRetainer::class)
            ->set('form.name', $retainer['name'])
            ->set('form.server', $retainer['server'])
            ->call('createRetainer');

        // Assert
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
        $retainer = [
            'name' => 'Retainer 11',
            'server' => 'Goblin',
        ];

        // Act
        $response = Livewire::actingAs($user)
            ->test(CreateRetainer::class)
            ->set('form.name', $retainer['name'])
            ->set('form.server', $retainer['server'])
            ->call('createRetainer');

        // Assert
        $response->assertHasErrors(['form.name' => 'You have reached the maximum number of 10 retainers.']);
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
        $retainer = [
            'name' => 'Retainer 1',
            'server' => 'Goblin',
        ];

        // Act
        $response = Livewire::actingAs($user)
            ->test(CreateRetainer::class)
            ->set('form.name', $retainer['name'])
            ->set('form.server', $retainer['server'])
            ->call('createRetainer');

        // Assert
        $response->assertHasErrors(['form.name' => 'You already have a retainer with that name.']);
    }
}
