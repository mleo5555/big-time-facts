<?php

namespace Database\Factories;

use App\Models\Player;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Player>
 */
class PlayerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'user_id' => null,
            'nickname' => fake()->unique()->firstName(),
        ];
    }

    /**
     * Indicate that the player joined while signed in to an account.
     */
    public function forUser(?User $user = null): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user ?? User::factory(),
        ]);
    }
}
