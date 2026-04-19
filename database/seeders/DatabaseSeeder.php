<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Users ─────────────────────────────────────────────────────────────

        $superAdmin = User::factory()->superAdmin()->create([
            'name'  => 'Super Admin',
            'email' => 'superadmin@zenner.test',
            'password' => Hash::make('password'),
        ]);

        $admins = collect([
            User::factory()->admin()->create(['name' => 'Alice Admin',   'email' => 'alice@zenner.test',   'password' => Hash::make('password')]),
            User::factory()->admin()->create(['name' => 'Bob Admin',     'email' => 'bob@zenner.test',     'password' => Hash::make('password')]),
        ]);

        $members = User::factory()->count(5)->create(['password' => Hash::make('password')]);

        $clients = collect([
            User::factory()->client()->create(['name' => 'Carol Client', 'email' => 'carol@client.test',  'password' => Hash::make('password')]),
            User::factory()->client()->create(['name' => 'Dave Client',  'email' => 'dave@client.test',   'password' => Hash::make('password')]),
            User::factory()->client()->create(['name' => 'Eve Client',   'email' => 'eve@client.test',    'password' => Hash::make('password')]),
        ]);

        $allInternalUsers = $admins->merge($members);

        // ── Projects ──────────────────────────────────────────────────────────

        $projectData = [
            ['name' => 'Website Redesign',    'color' => '#6366f1', 'description' => 'Full redesign of the company website including new branding and CMS migration.'],
            ['name' => 'Mobile App v2',        'color' => '#3b82f6', 'description' => 'Native iOS and Android app rebuild using React Native with new feature set.'],
            ['name' => 'Client Portal Launch', 'color' => '#22c55e', 'description' => 'Self-service client portal with billing, reporting, and ticket submission.'],
        ];

        foreach ($projectData as $i => $data) {
            $creator = $admins->get($i % $admins->count());

            $project = Project::create([
                'name'        => $data['name'],
                'description' => $data['description'],
                'slug'        => \Illuminate\Support\Str::slug($data['name']),
                'color'       => $data['color'],
                'is_archived' => false,
                'created_by'  => $creator->id,
            ]);

            // ── Statuses (3–5 per project) ────────────────────────────────────

            $statusDefs = [
                ['name' => 'Open',        'color' => '#6b7280', 'is_default' => true,  'is_closed' => false, 'sort_order' => 1],
                ['name' => 'In Progress', 'color' => '#3b82f6', 'is_default' => false, 'is_closed' => false, 'sort_order' => 2],
                ['name' => 'In Review',   'color' => '#f59e0b', 'is_default' => false, 'is_closed' => false, 'sort_order' => 3],
                ['name' => 'Blocked',     'color' => '#ef4444', 'is_default' => false, 'is_closed' => false, 'sort_order' => 4],
                ['name' => 'Done',        'color' => '#22c55e', 'is_default' => false, 'is_closed' => true,  'sort_order' => 5],
            ];

            $statuses = collect();
            foreach ($statusDefs as $def) {
                $statuses->push($project->statuses()->create($def));
            }

            // ── Project members ───────────────────────────────────────────────

            // Creator is always project admin
            $project->members()->attach($creator->id, ['project_role' => 'admin']);

            // Add 3–4 random members
            $members->random(rand(3, 4))->each(function ($member) use ($project) {
                $project->members()->attach($member->id, ['project_role' => 'member']);
            });

            // Add 1 client per project
            $client = $clients->get($i % $clients->count());
            $project->members()->attach($client->id, ['project_role' => 'client']);

            // ── Tasks (10–20 per project) ─────────────────────────────────────

            $projectMembers = $project->members()->wherePivot('project_role', '!=', 'client')->get();
            $taskCount = rand(10, 20);

            for ($t = 0; $t < $taskCount; $t++) {
                $status = $statuses->random();

                $task = Task::create([
                    'project_id'  => $project->id,
                    'status_id'   => $status->id,
                    'created_by'  => $projectMembers->random()->id,
                    'title'       => ucfirst(fake()->sentence(rand(4, 8), false)),
                    'description' => fake()->optional(0.7)->paragraphs(rand(1, 3), true),
                    'priority'    => fake()->randomElement(['low', 'normal', 'normal', 'high', 'urgent']),
                    'due_date'    => fake()->optional(0.6)->dateTimeBetween('-1 week', '+4 weeks'),
                    'sort_order'  => $t + 1,
                    'is_archived' => false,
                ]);

                // Assign 1–3 random members
                $assignees = $projectMembers->random(rand(1, min(3, $projectMembers->count())));
                $task->assignees()->sync($assignees->pluck('id')->toArray());

                // ── Comments (2–5 per task) ───────────────────────────────────

                $commentCount = rand(2, 5);
                $commenters   = $allInternalUsers->random(min($commentCount, $allInternalUsers->count()));

                foreach ($commenters as $commenter) {
                    // ~25% chance of an internal note
                    $isInternal = $projectMembers->contains($commenter) && fake()->boolean(25);

                    Comment::create([
                        'task_id'     => $task->id,
                        'user_id'     => $commenter->id,
                        'body'        => fake()->paragraphs(rand(1, 2), true),
                        'parent_id'   => null,
                        'is_internal' => $isInternal,
                    ]);
                }
            }
        }

        $this->command->info('✅ Zenner Tasks seeded successfully!');
        $this->command->info('   Super Admin : superadmin@zenner.test / password');
        $this->command->info('   Admin       : alice@zenner.test / password');
        $this->command->info('   Client      : carol@client.test / password');
    }
}
