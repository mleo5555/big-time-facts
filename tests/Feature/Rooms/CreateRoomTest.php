<?php

use App\Enums\RoomStatus;
use App\Models\Player;
use App\Models\Room;
use App\Models\User;
use App\Support\RoomCode;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery\MockInterface;

/*
|--------------------------------------------------------------------------
| Creating a room
|--------------------------------------------------------------------------
*/

test('a signed-in user can create a room', function () {
    $host = User::factory()->create();

    $response = $this->actingAs($host)->post(route('rooms.store'));

    // sole() fails the test unless exactly one room exists.
    $room = Room::sole();

    expect($room->host_id)->toBe($host->id)
        ->and($room->status)->toBe(RoomStatus::Lobby)
        ->and($room->code)->toMatch('/^['.RoomCode::ALPHABET.']{'.RoomCode::LENGTH.'}$/');

    $response->assertRedirect(route('rooms.host', $room));
});

test('guests are redirected to log in and no room is created', function () {
    $response = $this->post(route('rooms.store'));

    $response->assertRedirect(route('login'));
    expect(Room::count())->toBe(0);
});

test('users must verify their email before hosting', function () {
    $host = User::factory()->unverified()->create();

    $response = $this->actingAs($host)->post(route('rooms.store'));

    $response->assertRedirect(route('verification.notice'));
    expect(Room::count())->toBe(0);
});

test('a new code is generated when the first one is already taken', function () {
    Room::factory()->create(['code' => 'ABCD']);

    // Swap the real RoomCode for a fake whose first code collides with the room above.
    // twice() also fails the test if generate() isn't called exactly two times.
    $this->mock(RoomCode::class, function (MockInterface $mock) {
        $mock->shouldReceive('generate')->twice()->andReturn('ABCD', 'EFGH');
    });

    $this->actingAs(User::factory()->create())->post(route('rooms.store'));

    expect(Room::count())->toBe(2)
        ->and(Room::latest('id')->first()->code)->toBe('EFGH');
});

test('creating a room signs out any leftover player session', function () {
    // e.g. the host joined someone else's game on this laptop earlier. If that player
    // session lingered, it would take priority over the host on the room's presence channel.
    $host = User::factory()->create();
    $oldPlayer = Player::factory()->create();

    $this->actingAs($oldPlayer, 'player')
        ->actingAs($host)
        ->post(route('rooms.store'));

    $this->assertGuest('player');
    $this->assertAuthenticatedAs($host, 'web');
});

/*
|--------------------------------------------------------------------------
| The host screen
|--------------------------------------------------------------------------
*/

test('the host can view the host screen', function () {
    $room = Room::factory()->create();

    $response = $this->actingAs($room->host)->get(route('rooms.host', $room));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('rooms/Host')
            ->where('room.code', $room->code)
        );
});

test('other users cannot view the host screen', function () {
    $room = Room::factory()->create();

    $response = $this->actingAs(User::factory()->create())->get(route('rooms.host', $room));

    $response->assertForbidden();
});

test('guests are redirected to log in from the host screen', function () {
    $room = Room::factory()->create();

    $response = $this->get(route('rooms.host', $room));

    $response->assertRedirect(route('login'));
});
