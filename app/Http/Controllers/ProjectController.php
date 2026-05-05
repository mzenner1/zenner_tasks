<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Status;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Project::class);

        $projects = Project::query()->forUser(auth()->user())
            ->active()
            ->withCount('tasks')
            ->orderBy('name')
            ->get();

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        $this->authorize('create', Project::class);
        return view('projects.create');
    }

    public function store(StoreProjectRequest $request)
    {
        $this->authorize('create', Project::class);

        $slug = Str::slug($request->name);
        $original = $slug;
        $i = 1;
        while (Project::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $i++;
        }

        $project = Project::create([
            'name'        => $request->name,
            'description' => $request->description,
            'slug'        => $slug,
            'color'       => $request->color ?? '#6366f1',
            'created_by'  => auth()->id(),
        ]);

        // Seed 3 default statuses
        $statuses = [
            ['name' => 'Open',        'color' => '#6b7280', 'is_default' => true,  'is_closed' => false, 'sort_order' => 1],
            ['name' => 'In Progress', 'color' => '#3b82f6', 'is_default' => false, 'is_closed' => false, 'sort_order' => 2],
            ['name' => 'Done',        'color' => '#22c55e', 'is_default' => false, 'is_closed' => true,  'sort_order' => 3],
        ];

        foreach ($statuses as $status) {
            $project->statuses()->create($status);
        }

        // Add creator as project admin in project_members
        $project->members()->attach(auth()->id());

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    public function show(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        $user    = auth()->user();
        $members = $project->members;
        $statuses = $project->statuses;

        // ── Board view: tasks grouped per status column ───────────────────────
        if ($request->view === 'board') {
            $statuses->load(['tasks' => function ($q) use ($request) {
                $q->where('is_archived', false)
                  ->with(['assignees', 'creator', 'tags'])
                  ->when($request->assignee, fn ($q) => $q->whereHas('assignees', fn ($q2) => $q2->where('user_id', $request->assignee)))
                  ->when($request->priority,  fn ($q) => $q->where('priority', $request->priority))
                  ->when($request->search, function ($q) use ($request) {
                      $term = ltrim($request->search, '#');
                      $q->where(function ($q2) use ($term) {
                          $q2->where('title', 'like', '%' . $term . '%')
                             ->orWhere('task_number', is_numeric($term) ? (int) $term : -1);
                      });
                  })
                  ->orderBy('sort_order');
            }]);

            return view('projects.board', compact('project', 'statuses', 'members'));
        }

        // ── List view: flat task query with global sort & filters ───────────────

        // ── Persist / resolve sort ────────────────────────────────────────────
        if ($request->has('sort')) {
            $user->setProjectSortPreference($project->id, $request->sort ?: null);
        }

        $activeSort = $request->filled('sort')
            ? $request->sort
            : ($user->getProjectSortPreference($project->id) ?? 'updated');

        // ── Persist / resolve filters ─────────────────────────────────────────
        $filterKeys = ['status_ids', 'priorities', 'assignees', 'tag_ids'];
        $filtersInRequest = collect($filterKeys)->filter(fn ($k) => $request->has($k))->isNotEmpty();

        if ($request->boolean('clear_filters')) {
            $user->setProjectFiltersPreference($project->id, []);
            $activeFilters = [];
        } elseif ($filtersInRequest) {
            $savedFilters = [
                'status_ids' => $request->input('status_ids', []),
                'priorities' => $request->input('priorities', []),
                'assignees'  => $request->input('assignees', []),
                'tag_ids'    => $request->input('tag_ids', []),
            ];
            $user->setProjectFiltersPreference($project->id, $savedFilters);
            $activeFilters = $savedFilters;
        } else {
            $activeFilters = $user->getProjectFiltersPreference($project->id);
        }

        $filterStatusIds = array_filter((array) ($activeFilters['status_ids'] ?? []));
        $filterPriorities = array_filter((array) ($activeFilters['priorities'] ?? []));
        $filterAssignees  = array_filter((array) ($activeFilters['assignees']  ?? []));
        $filterTagIds     = array_filter((array) ($activeFilters['tag_ids']    ?? []));

        $tasksQuery = $project->tasks()
            ->where('is_archived', false)
            ->with(['assignees', 'status', 'creator', 'tags', 'latestComment'])
            ->withCount('comments')
            ->when(!empty($filterAssignees),  fn ($q) => $q->whereHas('assignees', fn ($q2) => $q2->whereIn('user_id', $filterAssignees)))
            ->when(!empty($filterStatusIds),  fn ($q) => $q->whereIn('status_id', $filterStatusIds))
            ->when(!empty($filterPriorities), fn ($q) => $q->whereIn('priority', $filterPriorities))
            ->when(!empty($filterTagIds),     fn ($q) => $q->whereHas('tags', fn ($q2) => $q2->whereIn('tags.id', $filterTagIds)))
            ->when($request->search, function ($q) use ($request) {
                $term = ltrim($request->search, '#');
                $q->where(function ($q2) use ($term) {
                    $q2->where('title', 'like', '%' . $term . '%')
                       ->orWhere('task_number', is_numeric($term) ? (int) $term : -1);
                });
            });

        match ($activeSort) {
            'title'        => $tasksQuery->orderBy('title', 'asc'),
            'priority'     => $tasksQuery->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'normal' THEN 3 WHEN 'low' THEN 4 END"),
            'due_date'     => $tasksQuery->orderByRaw('due_date IS NULL, due_date ASC'),
            'created'      => $tasksQuery->orderBy('created_at', 'desc'),
            'created_last' => $tasksQuery->orderBy('created_at', 'asc'),
            'updated_last' => $tasksQuery->orderByRaw('last_activity_at IS NULL DESC, last_activity_at ASC'),
            default        => $tasksQuery->orderByRaw('last_activity_at IS NULL, last_activity_at DESC'),
        };

        $totalTaskCount = $project->tasks()->where('is_archived', false)->count();
        $tasks = $tasksQuery->get();
        $filteredTaskCount = $tasks->count();

        $tags = $project->tags;

        $availableProjects = Project::forUser($user)
            ->where('id', '!=', $project->id)
            ->where('is_archived', false)
            ->orderBy('name')
            ->get();

        return view('projects.show', compact(
            'project', 'statuses', 'members', 'tasks', 'tags',
            'activeSort', 'filterStatusIds', 'filterPriorities', 'filterAssignees', 'filterTagIds',
            'totalTaskCount', 'filteredTaskCount', 'availableProjects'
        ));
    }

    public function edit(Project $project)
    {
        $this->authorize('update', $project);
        return view('projects.edit', compact('project'));
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        $project->update([
            'name'        => $request->name,
            'description' => $request->description,
            'color'       => $request->color,
        ]);

        return redirect()->route('projects.edit', $project)
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted.');
    }

    public function archive(Project $project)
    {
        $this->authorize('archive', $project);
        $project->update(['is_archived' => true]);

        return redirect()->route('projects.index')
            ->with('success', 'Project archived.');
    }

    public function restore(Project $project)
    {
        $this->authorize('archive', $project);
        $project->update(['is_archived' => false]);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project restored.');
    }
}
