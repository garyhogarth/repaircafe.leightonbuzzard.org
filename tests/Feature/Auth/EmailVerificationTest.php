<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Models\Role;

test('email verification screen can be rendered', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get('/verify-email');

    $response->assertStatus(200);
});

test('email can be verified', function () {
    $user = User::factory()->unverified()->create();

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    Event::assertDispatched(Verified::class);

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect(route('home', absolute: false).'?verified=1');
});

test('email is not verified with invalid hash', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong-email')]
    );

    $this->actingAs($user)->get($verificationUrl);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('verified plain members are redirected to the homepage from email verification screen', function () {
    $user = User::factory()->create();

    expect($user->hasVerifiedEmail())->toBeTrue();

    $response = $this->actingAs($user)->get('/verify-email');

    $response->assertRedirect(route('home', absolute: false));
});

test('verified volunteers are redirected to the dashboard from email verification screen', function () {
    Role::firstOrCreate(['name' => 'volunteer', 'guard_name' => 'web']);

    $user = User::factory()->create();
    $user->assignRole('volunteer');

    $response = $this->actingAs($user)->get('/verify-email');

    $response->assertRedirect(route('filament.dashboard.pages.dashboard', absolute: false));
});
