<?php

namespace Database\Factories;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tournament>
 */
class TournamentFactory extends Factory
{
    protected $model = Tournament::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true) . ' Tournament';

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'format' => 'single_elimination',
            'status' => 'draft',
            'max_teams' => 8,
            'min_teams' => 2,
            'team_size' => 5,
            'swiss_rounds' => null,
            'registration_starts_at' => now()->addDays(1),
            'registration_ends_at' => now()->addDays(7),
            'starts_at' => now()->addDays(14),
            'ends_at' => now()->addDays(15),
            'created_by' => User::factory(),
            'is_featured' => false,
            'require_approval' => false,
        ];
    }

    public function registrationOpen(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'registration_open',
            'registration_starts_at' => now()->subDay(),
            'registration_ends_at' => now()->addDays(7),
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'registration_starts_at' => now()->subDays(14),
            'registration_ends_at' => now()->subDays(7),
            'starts_at' => now()->subDay(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'registration_starts_at' => now()->subDays(30),
            'registration_ends_at' => now()->subDays(23),
            'starts_at' => now()->subDays(16),
            'ends_at' => now()->subDay(),
        ]);
    }

    public function singleElimination(): static
    {
        return $this->state(fn (array $attributes) => [
            'format' => 'single_elimination',
        ]);
    }

    public function doubleElimination(): static
    {
        return $this->state(fn (array $attributes) => [
            'format' => 'double_elimination',
        ]);
    }

    public function roundRobin(): static
    {
        return $this->state(fn (array $attributes) => [
            'format' => 'round_robin',
        ]);
    }

    public function swiss(): static
    {
        return $this->state(fn (array $attributes) => [
            'format' => 'swiss',
            'swiss_rounds' => 3,
        ]);
    }
}
