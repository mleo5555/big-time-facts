<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoomController extends Controller
{
    /**
     * Create a room hosted by the signed-in user.
     */
    public function store(Request $request): RedirectResponse
    {
        abort(501, 'Not implemented yet (Step D2).');
    }

    /**
     * The shared screen shown on the TV: room code and live player list.
     */
    public function host(Room $room): Response
    {
        // Step D2 adds authorization: only the room's host may view this.

        return Inertia::render('rooms/Host', [
            'room' => $room->only('id', 'code'),
        ]);
    }
}
