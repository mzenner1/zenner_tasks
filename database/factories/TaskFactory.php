<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id'  => Project::factory(),
            'status_id'   => Status::factory(),
            'created_by'  => User::factory(),
            'title'       => fake()->sentence(rand(4, 10), false),
            'description' => fake()->optional(0.7)->paragraphs(rand(1, 3), true),
            'priority'    => fake()->randomElement(['low', 'normal', 'normal', 'high', 'urgent']),
            'due_date'    => fake()->optional(0.6)->dateTimeBetween('-1 week', '+4 weeks'),
            'sort_order'  => fake()->numberBetween(0, 100),
            'is_archived' => false,
        ];
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'due_date' => fake()->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }

    public function urgent(): static
    {
        return $this->state(fn () => ['priority' => 'urgent']);
    }
}
