<?php

use App\Models\Player;
use App\Models\Room;
use App\Models\User;
use Illuminate\Testing\TestResponse;

/*
|--------------------------------------------------------------------------
| The room's presence channel
|--------------------------------------------------------------------------
|
| Tests run with the "null" broadcaster, which approves every channel request, so
| authorization tests against it would always pass. Switch to Reverb (no server
| needed; it only signs the response) and re-register the channels on it, since
| routes/channels.php registered them on the null broadcaster at boot.
|
*/

beforeEach(function () {
    config([
        'broadcasting.default' => 'reverb',
        'broadcasting.connections.reverb.key' => 'test-key',
        'broadcasting.connections.reverb.secret' => 'test-secret',
        'broadcasting.connections.reverb.app_id' => 'test-app',
    ]);

    require base_path('routes/channels.php');
});

function joinPresenceChannel(Room $room): TestResponse
{
    return test()->post('/broadcasting/auth', [
        'socket_id' => '1234.5678',
        'channel_name' => 'presence-rooms.'.$room->id,
    ]);
}

/**
 * The member data every other subscriber receives. It's visible in their browser's
 * DevTools, so tests pin it exactly: anything extra (an email, a user_id) is a leak.
 *
 * @return array{user_id: string, user_info: array<string, mixed>}
 */
function presenceMember(TestResponse $response): array
{
    return json_decode($response->json('channel_data'), true);
}

test('a player can join their room\'s channel', function () {
    $player = Player::factory()->create(['nickname' => 'Matt']);

    $this->actingAs($player, 'player');
    $response = joinPresenceChannel($player->room);

    $response->assertOk();

    expect(presenceMember($response))->toBe([
        'user_id' => 'player:'.$player->id,
        'user_info' => ['nickname' => 'Matt', 'role' => 'player'],
    ]);
});

test('a player cannot join another room\'s channel', function () {
    $player = Player::factory()->create();
    $otherRoom = Room::factory()->create();

    $this->actingAs($player, 'player');

    joinPresenceChannel($otherRoom)->assertForbidden();
});

test('the host can join their room\'s channel', function () {
    $room = Room::factory()->create();

    $this->actingAs($room->host);
    $response = joinPresenceChannel($room);

    $response->assertOk();

    expect(presenceMember($response))->toBe([
        'user_id' => 'user:'.$room->host_id,
        'user_info' => ['role' => 'host'],
    ]);
});

test('other signed-in users cannot join the channel', function () {
    $room = Room::factory()->create();

    $this->actingAs(User::factory()->create());

    joinPresenceChannel($room)->assertForbidden();
});

test('guests cannot join the channel', function () {
    $room = Room::factory()->create();

    joinPresenceChannel($room)->assertForbidden();
});

test('a signed-in user who joined as a player appears as that player', function () {
    // Both guards are logged in. The channel must check "player" first, or this person
    // would be resolved as a User, fail the host check, and be locked out of their game.
    $user = User::factory()->create();
    $player = Player::factory()->forUser($user)->create();

    $this->actingAs($user)->actingAs($player, 'player');
    $response = joinPresenceChannel($player->room);

    $response->assertOk();

    expect(presenceMember($response)['user_id'])->toBe('player:'.$player->id);
});
