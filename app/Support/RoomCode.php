<?php

namespace App\Support;

class RoomCode
{
    /**
     * Letters only, minus I and O, which are easily confused with 1 and 0 on a TV screen.
     * 24^4 gives ~330k possible codes.
     */
    public const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ';

    public const LENGTH = 4;

    /**
     * Generate a random room code. It doesn't check uniqueness: the unique index on
     * rooms.code is the source of truth, and room creation retries on a collision.
     *
     * An instance method (not static) so it can be resolved from the container and
     * swapped for a fake in tests, e.g. to force a collision.
     */
    public function generate(): string
    {
        $code = '';

        for ($i = 0; $i < self::LENGTH; $i++) {
            $code .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
        }

        return $code;
    }

    /**
     * Normalize a code typed by a player (" kxqm " => "KXQM").
     */
    public static function normalize(string $code): string
    {
        return strtoupper(trim($code));
    }
}
