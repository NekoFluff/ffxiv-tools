<?php

namespace Tests\Feature\Livewire;

use App\Livewire\RetainersDashboard;
use App\Models\Enums\DataCenter;
use App\Models\Enums\Server;
use App\Models\User;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RetainersDashboardTest extends TestCase
{
    #[Test]
    public function it_should_list_all_retainers(): void
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

        Livewire::withoutLazyLoading()->actingAs($user)
            ->test(RetainersDashboard::class)
            ->assertSee('Retainer 1')
            ->assertSee('Retainer 2');
    }
}
