<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Achievement>
 */
class AchievementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'slug' => \Str::slug($name),
            'name' => ucwords($name),
            'description' => fake()->sentence(),
            'icon' => fake()->randomElement(['star', 'trophy', 'sword', 'crosshair', 'shield']),
            'color' => fake()->randomElement(['green', 'blue', 'purple', 'orange', 'red']),
            'category' => fake()->randomElement(['combat', 'support', 'activity', 'special']),
            'stat_field' => fake()->randomElement(['kills', 'headshots', 'playtime', 'distance']),
            'threshold' => fake()->numberBetween(10, 1000),
            'points' => fake()->numberBetween(10, 200),
            'sort_order' => 0,
        ];
    }
}
