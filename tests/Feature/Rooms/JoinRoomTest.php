<?php

use App\Models\Player;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

/*
|--------------------------------------------------------------------------
| Joining a room
|--------------------------------------------------------------------------
*/

test('a player can join a room with its code and a nickname', function () {
    $room = Room::factory()->create();

    $response = $this->post(route('join.store'), [
        'code' => $room->code,
        'nickname' => 'Matt',
    ]);

    expect($room->players->count())->toBe(1)
        ->and($room->players->first()->nickname)->toBe('Matt');

    $this->assertAuthenticatedAs($room->players->first(), 'player');

    $response->assertRedirect(route('rooms.play', $room));
});

test('the code works regardless of case or surrounding spaces', function (string $typedCode) {
    $room = Room::factory()->create(['code' => 'ABCD']);

    $response = $this->post(route('join.store'), [
        'code' => $typedCode,
        'nickname' => 'Matt',
    ]);

    expect($room->players->count())->toBe(1);

    $response->assertRedirect(route('rooms.play', $room));
})->with([
    'lowercase' => 'abcd',
    'mixed case' => 'aBcD',
    'surrounding spaces' => '  ABCD ',
    'lowercase with spaces' => '  abcd ',
]);

test('an unknown code is rejected', function () {
    $room = Room::factory()->create(['code' => 'ABCD']);

    $response = $this->post(route('join.store'), [
        'code' => 'AAAA',
        'nickname' => 'Matt',
    ]);

    expect($room->players->count())->toBe(0);

    $response->assertSessionHasErrors('code');

    $this->assertGuest('player');
});

test('a nickname already taken in the room is rejected', function () {
    $room = Room::factory()->create();
    Player::factory()->create(['room_id' => $room->id, 'nickname' => 'Matt']);

    $response = $this->post(route('join.store'), [
        'code' => $room->code,
        'nickname' => 'Matt',
    ]);

    expect($room->players->count())->toBe(1);

    $response->assertSessionHasErrors('nickname');

    $this->assertGuest('player');
});

test('a nickname taken between validation and saving is rejected, not a server error', function () {
    // Simulates two phones joining as "Matt" at the same instant: validation sees the name
    // as free, then another player is inserted just before this request's own insert. Only
    // the unique index on (room_id, nickname) can catch it, and the controller must turn
    // that database error into the normal validation message.
    $room = Room::factory()->create();

    Player::creating(function () use ($room) {
        // A raw insert, so it doesn't fire this "creating" listener again.
        DB::table('players')->insert([
            'room_id' => $room->id,
            'nickname' => 'Matt',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    });

    $response = $this->post(route('join.store'), [
        'code' => $room->code,
        'nickname' => 'Matt',
    ]);

    expect($room->players()->count())->toBe(1);

    $response->assertSessionHasErrors(['nickname' => "That name's taken in this room. Try another!"]);

    $this->assertGuest('player');
});

test('the same nickname can be used in a different room', function () {
    Player::factory()->create(['nickname' => 'Matt']); // creates its own, separate room
    $room = Room::factory()->create();

    $response = $this->post(route('join.store'), [
        'code' => $room->code,
        'nickname' => 'Matt',
    ]);

    expect($room->players->count())->toBe(1);

    $response->assertRedirect(route('rooms.play', $room));
});

test('invalid nicknames are rejected', function (string $nickname) {
    $room = Room::factory()->create();

    $response = $this->post(route('join.store'), [
        'code' => $room->code,
        'nickname' => $nickname,
    ]);

    expect($room->players->count())->toBe(0);

    $response->assertSessionHasErrors('nickname');

    $this->assertGuest('player');
})->with([
    'empty' => '',
    'only spaces' => '   ',
    'too long (21 characters)' => str_repeat('a', 21),
]);

test('nicknames at the length limits are accepted', function (string $nickname) {
    $room = Room::factory()->create();

    $response = $this->post(route('join.store'), [
        'code' => $room->code,
        'nickname' => $nickname,
    ]);

    expect($room->players->sole()->nickname)->toBe($nickname);

    $response->assertRedirect(route('rooms.play', $room));
})->with([
    'one character' => 'M',
    'twenty characters' => str_repeat('a', 20),
]);

test('nicknames are saved without surrounding spaces', function () {
    $room = Room::factory()->create();

    $this->post(route('join.store'), [
        'code' => $room->code,
        'nickname' => '  Matt  ',
    ]);

    expect($room->players->sole()->nickname)->toBe('Matt');
});

test('players cannot join a finished room', function () {
    $room = Room::factory()->finished()->create();

    $response = $this->post(route('join.store'), [
        'code' => $room->code,
        'nickname' => 'Matt',
    ]);

    expect($room->players->count())->toBe(0);

    $response->assertSessionHasErrors('code');

    $this->assertGuest('player');
});

test('a signed-in user who joins is linked to their account', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create();

    $this->actingAs($user)->post(route('join.store'), [
        'code' => $room->code,
        'nickname' => 'Matt',
    ]);

    expect($room->players->sole()->user_id)->toBe($user->id);
});

test('guests who join are not linked to any account', function () {
    $room = Room::factory()->create();

    $this->post(route('join.store'), [
        'code' => $room->code,
        'nickname' => 'Matt',
    ]);

    expect($room->players->sole()->user_id)->toBeNull();
});

test('joining a new room replaces an earlier player session', function () {
    $oldPlayer = Player::factory()->create();
    $room = Room::factory()->create();

    $this->actingAs($oldPlayer, 'player')->post(route('join.store'), [
        'code' => $room->code,
        'nickname' => 'Matt',
    ]);

    $this->assertAuthenticatedAs($room->players->sole(), 'player');
});

/*
|--------------------------------------------------------------------------
| Rate limiting
|--------------------------------------------------------------------------
|
| Only failed attempts (wrong code) count toward the limit. A whole party joining
| from one home network shares a single IP address, so limiting every join per IP
| would lock out the 11th guest. Guessing codes is what we're trying to slow down.
|
*/

test('too many failed join attempts are rate limited', function () {
    Room::factory()->create(['code' => 'ABCD']);

    for ($i = 0; $i < 10; $i++) {
        $this->post(route('join.store'), ['code' => 'AAAA', 'nickname' => 'Matt'])
            ->assertSessionHasErrors('code');
    }

    $this->post(route('join.store'), ['code' => 'AAAA', 'nickname' => 'Matt'])
        ->assertTooManyRequests();
});

test('many successful joins from one network are not rate limited', function () {
    $room = Room::factory()->create();

    for ($i = 1; $i <= 15; $i++) {
        $this->post(route('join.store'), ['code' => $room->code, 'nickname' => "Player {$i}"])
            ->assertRedirect(route('rooms.play', $room));
    }

    expect($room->players()->count())->toBe(15);
});

/*
|--------------------------------------------------------------------------
| The join page and play screen
|--------------------------------------------------------------------------
*/

test('anyone can view the join page', function () {
    $response = $this->get(route('join'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('rooms/Join'));
});

test('a player can view their room\'s play screen', function () {
    $player = Player::factory()->create();

    $response = $this->actingAs($player, 'player')->get(route('rooms.play', $player->room));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('rooms/Play')
            ->where('room.code', $player->room->code)
            ->where('player.nickname', $player->nickname)
        );
});

test('a player cannot view another room\'s play screen', function () {
    $player = Player::factory()->create();
    $otherRoom = Room::factory()->create();

    $response = $this->actingAs($player, 'player')->get(route('rooms.play', $otherRoom));

    $response->assertForbidden();
});

test('guests are sent to the join page from the play screen', function () {
    $room = Room::factory()->create();

    $response = $this->get(route('rooms.play', $room));

    $response->assertRedirect(route('join'));
});

test('a signed-in user who has not joined is sent to the join page from the play screen', function () {
    // Being logged in on the "web" guard doesn't make you a player.
    $room = Room::factory()->create();

    $response = $this->actingAs(User::factory()->create())->get(route('rooms.play', $room));

    $response->assertRedirect(route('join'));
});
