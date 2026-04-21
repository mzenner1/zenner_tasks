<?php

use App\Models\Comment;
use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── Helpers ───────────────────────────────────────────────────────────────────

function createProjectWithMembers(): array
{
    $admin  = User::factory()->admin()->create();
    $member = User::factory()->create(['role' => 'member']);
    $client = User::factory()->client()->create();

    $project = Project::factory()->create(['created_by' => $admin->id]);

    // Seed the 3 default statuses
    $openStatus = $project->statuses()->create([
        'name' => 'Open', 'color' => '#6b7280', 'is_default' => true,
        'is_closed' => false, 'sort_order' => 1,
    ]);
    $doneStatus = $project->statuses()->create([
        'name' => 'Done', 'color' => '#22c55e', 'is_default' => false,
        'is_closed' => true, 'sort_order' => 2,
    ]);

    $project->members()->attach($admin->id);
    $project->members()->attach($member->id);
    $project->members()->attach($client->id);

    return compact('admin', 'member', 'client', 'project', 'openStatus', 'doneStatus');
}

// ── Test 1: Client cannot view a project they are not a member of ─────────────

test('client cannot view a project they are not a member of', function () {
    $client  = User::factory()->client()->create();
    $admin   = User::factory()->admin()->create();
    $project = Project::factory()->create(['created_by' => $admin->id]);
    $project->statuses()->create(['name'=>'Open','color'=>'#6b7280','is_default'=>true,'is_closed'=>false,'sort_order'=>1]);
    $project->members()->attach($admin->id);
    // client is NOT added to this project

    $response = $this->actingAs($client)->get(route('projects.show', $project));

    $response->assertForbidden();
});

// ── Test 2: Client cannot edit another user's task ────────────────────────────

test('client cannot edit another users task', function () {
    ['admin' => $admin, 'member' => $member, 'client' => $client,
     'project' => $project, 'openStatus' => $openStatus] = createProjectWithMembers();

    // Task created by member, not the client
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'status_id'  => $openStatus->id,
        'created_by' => $member->id,
    ]);

    $response = $this->actingAs($client)->put(
        route('projects.tasks.update', [$project, $task]),
        ['title' => 'Hacked title', 'status_id' => $openStatus->id]
    );

    $response->assertForbidden();
    $this->assertDatabaseMissing('tasks', ['id' => $task->id, 'title' => 'Hacked title']);
});

// ── Test 3: Client cannot see internal comments ───────────────────────────────

test('client cannot see internal comments on a task', function () {
    ['admin' => $admin, 'member' => $member, 'client' => $client,
     'project' => $project, 'openStatus' => $openStatus] = createProjectWithMembers();

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'status_id'  => $openStatus->id,
        'created_by' => $admin->id,
    ]);

    $internalComment = Comment::factory()->internal()->create([
        'task_id' => $task->id,
        'user_id' => $member->id,
        'body'    => 'This is a secret internal note.',
    ]);

    $publicComment = Comment::factory()->create([
        'task_id' => $task->id,
        'user_id' => $member->id,
        'body'    => 'This is a public comment.',
    ]);

    $response = $this->actingAs($client)->get(route('projects.tasks.show', [$project, $task]));

    $response->assertOk();
    $response->assertDontSee('This is a secret internal note.');
    $response->assertSee('This is a public comment.');
});

// ── Test 4: Admin can change any task's status in their project ───────────────

test('project admin can change any tasks status', function () {
    ['admin' => $admin, 'member' => $member,
     'project' => $project, 'openStatus' => $openStatus, 'doneStatus' => $doneStatus] = createProjectWithMembers();

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'status_id'  => $openStatus->id,
        'created_by' => $member->id,
    ]);

    $response = $this->actingAs($admin)->put(
        route('projects.tasks.update', [$project, $task]),
        ['title' => $task->title, 'status_id' => $doneStatus->id]
    );

    $response->assertRedirect();
    $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status_id' => $doneStatus->id]);
});

// ── Test 5: Member cannot delete a task ──────────────────────────────────────

test('member cannot delete a task', function () {
    ['member' => $member, 'project' => $project, 'openStatus' => $openStatus] = createProjectWithMembers();

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'status_id'  => $openStatus->id,
        'created_by' => $member->id,
    ]);

    $response = $this->actingAs($member)->delete(
        route('projects.tasks.destroy', [$project, $task])
    );

    $response->assertForbidden();
    $this->assertDatabaseHas('tasks', ['id' => $task->id]);
});

// ── Test 6: Client can edit their own task ────────────────────────────────────

test('client can edit their own task', function () {
    ['client' => $client, 'project' => $project, 'openStatus' => $openStatus] = createProjectWithMembers();

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'status_id'  => $openStatus->id,
        'created_by' => $client->id, // owned by the client
    ]);

    $response = $this->actingAs($client)->put(
        route('projects.tasks.update', [$project, $task]),
        ['title' => 'My updated title', 'status_id' => $openStatus->id]
    );

    $response->assertRedirect();
    $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'My updated title']);
});
