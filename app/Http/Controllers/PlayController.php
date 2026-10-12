<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;

class PlayController extends Controller
{
    /**
     * The player's phone screen for a room.
     */
    public function show(Request $request, Room $room): Response
    {
        Gate::authorize('play', $room);

        return Inertia::render('rooms/Play', [
            'room' => $room->only('id', 'code'),
            'player' => $request->user('player')->only('nickname'),
        ]);
    }
}
