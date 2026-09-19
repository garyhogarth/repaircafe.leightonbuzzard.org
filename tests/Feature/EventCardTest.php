<?php

use App\Livewire\EventCard;
use App\Models\Category;
use App\Models\Event;
use App\Models\Item;
use App\Models\User;
use App\Models\Venue;
use Livewire\Livewire;

beforeEach(function () {
    Venue::factory()->create();
    Category::factory()->create();
});

test('guests attending excludes both volunteers and fixers', function () {
    $event = Event::factory()->create();

    $event->users()->attach(User::factory()->create(), ['volunteer' => true, 'fixer' => false]);
    $event->users()->attach(User::factory()->create(), ['volunteer' => true, 'fixer' => true]);
    $event->users()->attach(User::factory()->create(), ['volunteer' => false, 'fixer' => false]);
    $event->users()->attach(User::factory()->create(), ['volunteer' => false, 'fixer' => false]);

    $event->refresh();

    $component = Livewire::test(EventCard::class, ['event' => $event])
        ->assertSee('Guests attending');

    expect($component->instance()->guestsCount())->toBe(2);
});

test('shows how many items the authenticated user has booked into the event', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();

    $event->items()->attach(Item::factory()->create(['user_id' => $user->id]));
    $event->items()->attach(Item::factory()->create(['user_id' => $user->id]));
    $event->items()->attach(Item::factory()->create(['user_id' => User::factory()->create()->id]));

    $event->refresh();

    $component = Livewire::actingAs($user)
        ->test(EventCard::class, ['event' => $event])
        ->assertSee('Your items booked in')
        ->assertSee('Book another item in');

    expect($component->instance()->myItemsCount())->toBe(2);
});

test('prompts a guest with no items yet to book one in', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();

    Livewire::actingAs($user)
        ->test(EventCard::class, ['event' => $event])
        ->assertSee('Book an item in')
        ->assertDontSee('Book another item in');
});
