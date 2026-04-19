<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(rand(2, 4), true);
        return [
            'name'        => ucwords($name),
            'description' => fake()->sentence(rand(10, 20)),
            'slug'        => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 9999),
            'color'       => fake()->randomElement(['#6366f1','#3b82f6','#22c55e','#f59e0b','#ef4444','#8b5cf6','#ec4899','#14b8a6']),
            'is_archived' => false,
            'created_by'  => User::factory(),
        ];
    }

    public function archived(): static
    {
        return $this->state(fn () => ['is_archived' => true]);
    }
}
