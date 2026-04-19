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

        $projects = Project::scopeForUser(Project::query(), auth()->user())
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
        $project->members()->attach(auth()->id(), ['project_role' => 'admin']);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    public function show(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        $statuses = $project->statuses()->with(['tasks' => function ($q) use ($request, $project) {
            $q->where('is_archived', false)
              ->with(['assignees', 'creator'])
              ->when($request->assignee, fn ($q) => $q->whereHas('assignees', fn ($q2) => $q2->where('user_id', $request->assignee)))
              ->when($request->priority,  fn ($q) => $q->where('priority', $request->priority))
              ->when($request->search,    fn ($q) => $q->where('title', 'like', '%' . $request->search . '%'))
              ->orderBy('sort_order');
        }])->get();

        $members = $project->members;

        if ($request->view === 'board') {
            return view('projects.board', compact('project', 'statuses', 'members'));
        }

        return view('projects.show', compact('project', 'statuses', 'members'));
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
