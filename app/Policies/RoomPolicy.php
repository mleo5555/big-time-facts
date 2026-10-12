<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;
use App\Models\Player;

class RoomPolicy
{
    /**
     * Determine whether the user is the room's host, and so may use its host screen
     * and host-only actions.
     */
    public function host(User $user, Room $room): bool
    {
        return $room->host()->is($user);
    }

    /**
     * Determine whether the player belongs to the room, and so may use its play screen
     * and player actions.
     */
    public function play(Player $player, Room $room): bool
    {
        return $player->room()->is($room);
    }
}
