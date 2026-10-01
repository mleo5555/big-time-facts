<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlayController extends Controller
{
    /**
     * The player's phone screen for a room.
     */
    public function show(Request $request, Room $room): Response
    {
        // Step D4 adds authorization: only players in this room may view this.

        return Inertia::render('rooms/Play', [
            'room' => $room->only('id', 'code'),
            'player' => $request->user('player')->only('nickname'),
        ]);
    }
}
