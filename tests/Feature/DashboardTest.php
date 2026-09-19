<?php

use App\Filament\Dashboard\Resources\Items\Pages\CreateItem;
use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

test('guests are redirected to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('authenticated users with verified email can visit the dashboard', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $this->actingAs($user);

    $this->get('/dashboard')->assertStatus(200);
});

test('authenticated users without verified email are redirected to verify-email', function () {
    $user = User::factory()->create(['email_verified_at' => null]);

    $this->actingAs($user);

    $this->get('/dashboard')->assertRedirect(route('verification.notice'));
});

test('any authenticated user (no special permissions) can book an item in via the dashboard', function () {
    Category::factory()->create();
    $user = User::factory()->create(['email_verified_at' => now()]);

    $this->actingAs($user)
        ->get('/dashboard/items/create')
        ->assertSuccessful();
});

test('any authenticated user can create their own item via the dashboard', function () {
    Filament::setCurrentPanel('dashboard');

    $category = Category::factory()->create();
    $user = User::factory()->create(['email_verified_at' => now()]);

    Livewire::actingAs($user)
        ->test(CreateItem::class)
        ->fillForm([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'description' => 'My kettle',
            'issue' => "Won't switch on",
            'status' => 'broken',
            'powered' => 'mains',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Item::class, [
        'description' => 'My kettle',
        'user_id' => $user->id,
    ]);
});
