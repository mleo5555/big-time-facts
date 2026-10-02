<?php

namespace App\Http\Controllers;

use App\Http\Requests\JoinRoomRequest;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class JoinRoomController extends Controller
{
    /**
     * Join a room with its code and a nickname, and sign in as the new player.
     */
    public function store(JoinRoomRequest $request): RedirectResponse
    {
        $room = $request->room();
        $player = $room->players()->make(['nickname' => $request->validated('nickname')]);
        $player->user()->associate($request->user('web'));   // null for guests

        try {
            $player->save();
        } catch (UniqueConstraintViolationException) {
            // Addresses rare race condition possibility for matching nicknames
            throw ValidationException::withMessages([
                'nickname' => "That name's taken in this room. Try another!",
            ]);
        }

        Auth::guard('player')->login($player);
        $request->session()->regenerate();

        return redirect()->route('rooms.play', $room);
    }
}
