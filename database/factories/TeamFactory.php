<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'tag' => strtoupper(fake()->unique()->lexify('???')),
            'description' => fake()->sentence(),
            'captain_id' => User::factory(),
            'is_active' => true,
            'is_verified' => false,
            'is_recruiting' => true,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
        ]);
    }

    public function disbanded(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'disbanded_at' => now(),
        ]);
    }

    public function notRecruiting(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_recruiting' => false,
        ]);
    }
}
