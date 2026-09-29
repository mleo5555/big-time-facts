<?php

namespace App\Models;

use Database\Factories\PlayerFactory;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A guest's seat in one room. Authenticated through the session-based "player" guard.
 *
 * @property int $id
 * @property int $room_id
 * @property int|null $user_id
 * @property string $nickname
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['nickname'])]
class Player extends Model implements AuthenticatableContract
{
    /** @use HasFactory<PlayerFactory> */
    use Authenticatable, HasFactory;

    /**
     * Players have no "remember me" token column. An empty name makes the guard skip it.
     * (A method override, because PHP won't let a class redeclare a trait's property.)
     */
    public function getRememberTokenName(): string
    {
        return '';
    }

    /**
     * Presence channels key members by this value. Prefixed so Player #1 and User #1
     * (a host) don't collide and get merged into one member.
     */
    public function getAuthIdentifierForBroadcasting(): string
    {
        return 'player:'.$this->getKey();
    }

    /**
     * @return BelongsTo<Room, $this>
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * The account this player is linked to, if they were signed in when they joined.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
