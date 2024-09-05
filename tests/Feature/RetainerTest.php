<?php

use App\Models\Item;
use App\Models\User;
use Livewire\Volt\Volt;

// test('profile page is displayed', function () {
//     $user = User::factory()->create();

//     $this->actingAs($user);

//     $response = $this->get('/profile');

//     $response
//         ->assertOk()
//         ->assertSeeVolt('profile.update-profile-information-form')
//         ->assertSeeVolt('profile.update-password-form')
//         ->assertSeeVolt('profile.delete-user-form');
// });

// test('profile information can be updated', function () {
//     $user = User::factory()->create();

//     $this->actingAs($user);

//     $component = Volt::test('profile.update-profile-information-form')
//         ->set('name', 'Test User')
//         ->set('email', 'test@example.com')
//         ->call('updateProfileInformation');

//     $component
//         ->assertHasNoErrors()
//         ->assertNoRedirect();

//     $user->refresh();

//     $this->assertSame('Test User', $user->name);
//     $this->assertSame('test@example.com', $user->email);
//     $this->assertNull($user->email_verified_at);
// });

// test('email verification status is unchanged when the email address is unchanged', function () {
//     $user = User::factory()->create();

//     $this->actingAs($user);

//     $component = Volt::test('profile.update-profile-information-form')
//         ->set('name', 'Test User')
//         ->set('email', $user->email)
//         ->call('updateProfileInformation');

//     $component
//         ->assertHasNoErrors()
//         ->assertNoRedirect();

//     $this->assertNotNull($user->refresh()->email_verified_at);
// });

test('user must own retainer to add an item to it', function () {
    $user = User::factory()->create();

    $retainer = User::factory()->create()->retainers()->create([
        'name' => 'Test Retainer',
        'server' => 'Goblin',
        'data_center' => 'Crystal',
    ]);

    $item = Item::factory()->create();

    $this->actingAs($user);

    $component = Volt::test('retainer.add-retainer-item-form', [
        'retainer' => $retainer,
    ])
        ->set('selectedItemID', $item->id)
        ->call('addItem');

    $component->assertForbidden();
});

test('it should save a new item for a retainer', function () {
    $user = User::factory()->create();
    $retainer = $user->retainers()->create([
        'name' => 'Test Retainer',
        'server' => 'Goblin',
        'data_center' => 'Crystal',
    ]);

    $item = Item::factory()->create();

    $this->actingAs($user);

    $component = Volt::test('retainer.add-retainer-item-form', [
        'retainer' => $retainer,
    ])
        ->set('selectedItemID', $item->id)
        ->set('search', $item->name)
        ->call('addItem');

    $component
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertDatabaseHas('item_retainer', [
        'retainer_id' => $retainer->id,
        'item_id' => $item->id,
    ]);
});

test('there is an error when the search input field is empty', function () {
    $user = User::factory()->create();
    $retainer = $user->retainers()->create([
        'name' => 'Test Retainer',
        'server' => 'Goblin',
        'data_center' => 'Crystal',
    ]);

    $this->actingAs($user);

    $component = Volt::test('retainer.add-retainer-item-form', [
        'retainer' => $retainer,
    ])
        ->set('search', '')
        ->call('addItem');

    $component
        ->assertHasErrors(['search' => ['required']]);
});

test('there is an error when no item can be found', function () {
    $user = User::factory()->create();
    $retainer = $user->retainers()->create([
        'name' => 'Test Retainer',
        'server' => 'Goblin',
        'data_center' => 'Crystal',
    ]);

    $this->actingAs($user);

    $component = Volt::test('retainer.add-retainer-item-form', [
        'retainer' => $retainer,
    ])
        ->set('search', 'asdf')
        ->call('addItem');

    $component
        ->assertHasErrors(['search' => 'Item not found.']);
});

test('there is an error when an item is already attached to a retainer', function () {
    $user = User::factory()->create();
    $retainer = $user->retainers()->create([
        'name' => 'Test Retainer',
        'server' => 'Goblin',
        'data_center' => 'Crystal',
    ]);

    $item = Item::factory()->create();

    $retainer->items()->attach($item);

    $this->actingAs($user);

    $component = Volt::test('retainer.add-retainer-item-form', [
        'retainer' => $retainer,
    ])
        ->set('selectedItemID', $item->id)
        ->set('search', $item->name)
        ->call('addItem');

    $component
        ->assertHasErrors(['search' => 'Item already added.']);

    $this->assertDatabaseHas('item_retainer', [
        'retainer_id' => $retainer->id,
        'item_id' => $item->id,
    ]);
});

test('no additional items can be added if the retainer has 20 items', function () {
    $user = User::factory()->create();
    $retainer = $user->retainers()->create([
        'name' => 'Test Retainer',
        'server' => 'Goblin',
        'data_center' => 'Crystal',
    ]);

    $items = Item::factory()->count(20)->create();

    $retainer->items()->attach($items);

    $item = Item::factory()->create();

    $this->actingAs($user);

    $component = Volt::test('retainer.add-retainer-item-form', [
        'retainer' => $retainer,
    ])
        ->set('selectedItemID', $item->id)
        ->set('search', $item->name)
        ->call('addItem');

    $component
        ->assertHasErrors(['search' => 'You can only add up to 20 items.']);

    $this->assertDatabaseMissing('item_retainer', [
        'retainer_id' => $retainer->id,
        'item_id' => $item->id,
    ]);
});

test('user must own retainer to remove an item from it', function () {
    $user = User::factory()->create();

    $retainer = User::factory()->create()->retainers()->create([
        'name' => 'Test Retainer',
        'server' => 'Goblin',
        'data_center' => 'Crystal',
    ]);

    $item = Item::factory()->create();

    $retainer->items()->attach($item);

    $this->actingAs($user);

    $component = Volt::test('retainer.manage-retainer-items', [
        'retainer' => $retainer,
    ])
        ->call('removeItem', $item->id);

    $component->assertForbidden();
});

test('it should remove an item from a retainer', function () {
    $user = User::factory()->create();
    $retainer = $user->retainers()->create([
        'name' => 'Test Retainer',
        'server' => 'Goblin',
        'data_center' => 'Crystal',
    ]);

    $item = Item::factory()->create();

    $retainer->items()->attach($item);

    $this->actingAs($user);

    $component = Volt::test('retainer.manage-retainer-items', [
        'retainer' => $retainer,
    ])
        ->call('removeItem', $item->id);

    $component
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('item_retainer', [
        'retainer_id' => $retainer->id,
        'item_id' => $item->id,
    ]);
});

test('user must own retainer to delete it', function () {
    $user = User::factory()->create();

    $retainer = User::factory()->create()->retainers()->create([
        'name' => 'Test Retainer',
        'server' => 'Goblin',
        'data_center' => 'Crystal',
    ]);

    $this->actingAs($user);

    $component = Volt::test('retainer.delete-retainer-form', [
        'retainer' => $retainer,
    ])
        ->set('name', $retainer->name)
        ->call('deleteRetainer');

    $component->assertForbidden();
});

test('user can delete their retainer', function () {
    $user = User::factory()->create();

    $retainer = $user->retainers()->create([
        'name' => 'Test Retainer',
        'server' => 'Goblin',
        'data_center' => 'Crystal',
    ]);

    $this->actingAs($user);

    $component = Volt::test('retainer.delete-retainer-form', [
        'retainer' => $retainer,
    ])
        ->set('name', $retainer->name)
        ->call('deleteRetainer');

    $component
        ->assertHasNoErrors();

    $this->assertNull($user->retainers()->find($retainer->id));
});

test('correct name must be provided to delete the retainer', function () {
    $user = User::factory()->create();

    $retainer = $user->retainers()->create([
        'name' => 'Test Retainer',
        'server' => 'Goblin',
        'data_center' => 'Crystal',
    ]);

    $this->actingAs($user);

    $component = Volt::test('retainer.delete-retainer-form', [
        'retainer' => $retainer,
    ])
        ->set('name', 'asdf')
        ->call('deleteRetainer');

    $component
        ->assertHasErrors('name');

    $this->assertNotNull($user->retainers()->find($retainer->id));
});
