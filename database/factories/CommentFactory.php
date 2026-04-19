<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'task_id'     => Task::factory(),
            'user_id'     => User::factory(),
            'body'        => fake()->paragraph(rand(1, 4)),
            'parent_id'   => null,
            'is_internal' => false,
        ];
    }

    public function internal(): static
    {
        return $this->state(fn () => ['is_internal' => true]);
    }
}
