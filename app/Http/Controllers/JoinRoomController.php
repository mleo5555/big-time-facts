<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JoinRoomController extends Controller
{
    /**
     * Join a room with its code and a nickname, and sign in as the new player.
     */
    public function store(Request $request): RedirectResponse
    {
        abort(501, 'Not implemented yet (Step D3).');
    }
}
