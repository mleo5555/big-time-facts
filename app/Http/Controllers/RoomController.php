<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Actions\CreateRoom;
use Illuminate\Support\Facades\Gate;

class RoomController extends Controller
{
    /**
     * Create a room hosted by the signed-in user.
     */
    public function store(Request $request, CreateRoom $createRoom): RedirectResponse
    {
        $room = $createRoom->handle($request->user('web'));
        auth('player')->logout();

        return redirect()->route('rooms.host', $room);
    }

    /**
     * The shared screen shown on the TV: room code and live player list.
     */
    public function host(Room $room): Response
    {
        Gate::authorize('host', $room);

        return Inertia::render('rooms/Host', [
            'room' => $room->only('id', 'code'),
        ]);
    }
}
