<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;

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
}
