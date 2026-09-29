<?php

namespace Database\Factories;

use App\Enums\RoomStatus;
use App\Models\Room;
use App\Models\User;
use App\Support\RoomCode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // unique() stops two rooms in one test from randomly getting the same code and
            // tripping the unique index, which would make tests fail at random.
            'code' => fake()->unique()->regexify('['.RoomCode::ALPHABET.']{'.RoomCode::LENGTH.'}'),
            'host_id' => User::factory(),
            'status' => RoomStatus::Lobby,
        ];
    }

    /**
     * Indicate that a game is being played in the room.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RoomStatus::InProgress,
        ]);
    }

    /**
     * Indicate that the room's session has ended.
     */
    public function finished(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RoomStatus::Finished,
        ]);
    }
}
