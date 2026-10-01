<?php

use App\Http\Controllers\JoinRoomController;
use App\Http\Controllers\PlayController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    // Hosting: a verified account is required.
    Route::post('rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::get('rooms/{room:code}/host', [RoomController::class, 'host'])->name('rooms.host');
});

// Joining: open to guests. Joining signs you in on the "player" guard.
Route::inertia('join', 'rooms/Join')->name('join');
Route::post('join', [JoinRoomController::class, 'store'])->name('join.store');

// Playing: requires a player session. Logged-out visitors are sent to /join (see bootstrap/app.php).
Route::get('rooms/{room:code}/play', [PlayController::class, 'show'])
    ->middleware('auth:player')
    ->name('rooms.play');

require __DIR__.'/settings.php';
