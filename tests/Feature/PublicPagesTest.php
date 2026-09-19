<?php

use App\Models\Event;
use App\Models\User;
use App\Models\Venue;

test('home page is accessible', function () {
    $response = $this->get('/');

    $response->assertSuccessful();
    $response->assertSee('Don\'t bin it, repair it!', false);
});

test('guests see login and register links in the header', function () {
    $response = $this->get('/');

    $response->assertSee('Log in');
    $response->assertSee('Register');
    $response->assertDontSee('Account Settings');
});

test('authenticated users see an account dropdown in the header', function () {
    $user = User::factory()->create(['name' => 'Jordan Example']);

    $response = $this->actingAs($user)->get('/');

    $response->assertSee('Dashboard');
    $response->assertSee('Jordan Example');
    $response->assertSee('Account Settings');
    $response->assertSee('Log out');
    $response->assertDontSee('Log in');
});

test('home page shows a message when there is no upcoming event', function () {
    $response = $this->get('/');

    $response->assertSuccessful();
    $response->assertSee('Check back soon for details of the next event!');
});

test('home page shows the next upcoming event', function () {
    $venue = Venue::factory()->create(['name' => 'The Community Hall']);

    Event::factory()->create([
        'venue_id' => $venue->id,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->subDay()->addHours(4),
    ]);

    $nextEvent = Event::factory()->create([
        'venue_id' => $venue->id,
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDay()->addHours(4),
    ]);

    $response = $this->get('/');

    $response->assertSuccessful();
    $response->assertSee('Our next Repair Café event is:');
    $response->assertSee('The Community Hall');
    $response->assertSee($nextEvent->starts_at->format('l jS \o\f F'), false);
});

test('more information page is accessible', function () {
    $response = $this->get(route('more-information'));

    $response->assertSuccessful();
    $response->assertSee('More Information');
});

test('policies page is accessible', function () {
    $response = $this->get(route('policies'));

    $response->assertSuccessful();
    $response->assertSee('Our Policies');
    $response->assertSee('Privacy Policy');
    $response->assertSee('Health & Safety Policy');
    $response->assertSee('Volunteer Policy');
});

test('repair disclaimer page is accessible', function () {
    $response = $this->get(route('repair-disclaimer'));

    $response->assertSuccessful();
    $response->assertSee('Repair Disclaimer');
    $response->assertSee('No Guarantee of Repair');
});

test('contact page is accessible', function () {
    $response = $this->get(route('contact'));

    $response->assertSuccessful();
    $response->assertSee('Contact Us');
    $response->assertSee('Get In Touch');
});
