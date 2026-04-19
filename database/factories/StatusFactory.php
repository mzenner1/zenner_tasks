<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class StatusFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name'       => fake()->randomElement(['Open', 'In Progress', 'In Review', 'Blocked', 'Done', 'Closed']),
            'color'      => fake()->randomElement(['#6b7280','#3b82f6','#f59e0b','#ef4444','#22c55e','#8b5cf6']),
            'is_default' => false,
            'is_closed'  => false,
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }

    public function default(): static
    {
        return $this->state(fn () => ['is_default' => true]);
    }

    public function closed(): static
    {
        return $this->state(fn () => ['is_closed' => true]);
    }
}
