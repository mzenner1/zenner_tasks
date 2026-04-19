<?php

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createKanbanSetup(): array
{
    $admin  = User::factory()->admin()->create();
    $member = User::factory()->create(['role' => 'member']);
    $client = User::factory()->client()->create();

    $project = Project::factory()->create(['created_by' => $admin->id]);

    $openStatus = $project->statuses()->create([
        'name' => 'Open', 'color' => '#6b7280', 'is_default' => true,
        'is_closed' => false, 'sort_order' => 1,
    ]);
    $inProgressStatus = $project->statuses()->create([
        'name' => 'In Progress', 'color' => '#3b82f6', 'is_default' => false,
        'is_closed' => false, 'sort_order' => 2,
    ]);
    $doneStatus = $project->statuses()->create([
        'name' => 'Done', 'color' => '#22c55e', 'is_default' => false,
        'is_closed' => true, 'sort_order' => 3,
    ]);

    $project->members()->attach($admin->id,  ['project_role' => 'admin']);
    $project->members()->attach($member->id, ['project_role' => 'member']);
    $project->members()->attach($client->id, ['project_role' => 'client']);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'status_id'  => $openStatus->id,
        'created_by' => $admin->id,
        'sort_order' => 0,
    ]);

    return compact('admin', 'member', 'client', 'project', 'task', 'openStatus', 'inProgressStatus', 'doneStatus');
}

// ── Test 7: Kanban move updates status_id and sort_order ─────────────────────

test('dragging a task updates status_id and sort_order', function () {
    ['admin' => $admin, 'task' => $task, 'inProgressStatus' => $inProgressStatus] = createKanbanSetup();

    $response = $this->actingAs($admin)->postJson(
        route('tasks.move', $task),
        ['status_id' => $inProgressStatus->id, 'sort_order' => 2]
    );

    $response->assertOk()->assertJson(['success' => true]);

    $this->assertDatabaseHas('tasks', [
        'id'         => $task->id,
        'status_id'  => $inProgressStatus->id,
        'sort_order' => 2,
    ]);
});

test('member can move a task via kanban', function () {
    ['member' => $member, 'task' => $task, 'inProgressStatus' => $inProgressStatus] = createKanbanSetup();

    $response = $this->actingAs($member)->postJson(
        route('tasks.move', $task),
        ['status_id' => $inProgressStatus->id, 'sort_order' => 0]
    );

    $response->assertOk()->assertJson(['success' => true]);
    $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status_id' => $inProgressStatus->id]);
});

test('client can move their own task via kanban', function () {
    ['client' => $client, 'project' => $project, 'openStatus' => $openStatus, 'inProgressStatus' => $inProgressStatus] = createKanbanSetup();

    // Task owned by the client
    $clientTask = Task::factory()->create([
        'project_id' => $project->id,
        'status_id'  => $openStatus->id,
        'created_by' => $client->id,
        'sort_order' => 0,
    ]);

    $response = $this->actingAs($client)->postJson(
        route('tasks.move', $clientTask),
        ['status_id' => $inProgressStatus->id, 'sort_order' => 1]
    );

    $response->assertOk()->assertJson(['success' => true]);
    $this->assertDatabaseHas('tasks', ['id' => $clientTask->id, 'status_id' => $inProgressStatus->id]);
});

test('kanban move requires valid status_id', function () {
    ['admin' => $admin, 'task' => $task] = createKanbanSetup();

    $response = $this->actingAs($admin)->postJson(
        route('tasks.move', $task),
        ['status_id' => 99999, 'sort_order' => 0]
    );

    $response->assertUnprocessable();
});

test('kanban move requires sort_order', function () {
    ['admin' => $admin, 'task' => $task, 'inProgressStatus' => $inProgressStatus] = createKanbanSetup();

    $response = $this->actingAs($admin)->postJson(
        route('tasks.move', $task),
        ['status_id' => $inProgressStatus->id]
    );

    $response->assertUnprocessable();
});

test('unauthenticated user cannot move a task', function () {
    $setup = createKanbanSetup(); $task = $setup['task']; $inProgressStatus = $setup['inProgressStatus'];

    $response = $this->postJson(
        route('tasks.move', $task),
        ['status_id' => $inProgressStatus->id, 'sort_order' => 0]
    );

    $response->assertUnauthorized();
});
