<?php

namespace App\Actions;

use App\Models\Room;
use App\Models\User;
use App\Support\RoomCode;
use Illuminate\Database\UniqueConstraintViolationException;
use Throwable;

class CreateRoom
{
    public function __construct(private RoomCode $codes) {}

    /**
     * Create a room hosted by the given user, with a code no other room is using.
     *
     * Uniqueness is guaranteed by the unique index on rooms.code, not by checking first:
     * a check-then-insert can race with another host. So insert, and if the code turns
     * out to be taken, try again with a new one.
     */
    public function handle(User $host): Room
    {
        return retry(
            5,
            fn () => $host->hostedRooms()->create(['code' => $this->codes->generate()]),
            when: fn (Throwable $e) => $e instanceof UniqueConstraintViolationException,
        );
    }
}
