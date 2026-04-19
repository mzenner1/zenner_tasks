<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Status;
use App\Http\Requests\StoreStatusRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Http\Requests\ReorderStatusRequest;

class StatusController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('manageStatuses', $project);

        $statuses = $project->statuses()->withCount('tasks')->get();

        return view('statuses.index', compact('project', 'statuses'));
    }

    public function create(Project $project)
    {
        $this->authorize('manageStatuses', $project);
        return view('statuses.create', compact('project'));
    }

    public function store(StoreStatusRequest $request, Project $project)
    {
        $this->authorize('manageStatuses', $project);

        $maxOrder = $project->statuses()->max('sort_order') ?? 0;

        $project->statuses()->create([
            'name'       => $request->name,
            'color'      => $request->color,
            'is_closed'  => $request->boolean('is_closed'),
            'is_default' => $request->boolean('is_default'),
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()->route('projects.statuses.index', $project)
            ->with('success', 'Status created.');
    }

    public function edit(Project $project, Status $status)
    {
        $this->authorize('manageStatuses', $project);
        return view('statuses.edit', compact('project', 'status'));
    }

    public function update(UpdateStatusRequest $request, Project $project, Status $status)
    {
        $this->authorize('manageStatuses', $project);

        $status->update([
            'name'       => $request->name,
            'color'      => $request->color,
            'is_closed'  => $request->boolean('is_closed'),
            'is_default' => $request->boolean('is_default'),
        ]);

        return redirect()->route('projects.statuses.index', $project)
            ->with('success', 'Status updated.');
    }

    public function destroy(Project $project, Status $status)
    {
        $this->authorize('manageStatuses', $project);

        if ($status->tasks()->count() > 0) {
            return redirect()->route('projects.statuses.index', $project)
                ->with('error', 'Cannot delete a status that has tasks assigned to it. Reassign the tasks first.');
        }

        $status->delete();

        return redirect()->route('projects.statuses.index', $project)
            ->with('success', 'Status deleted.');
    }

    public function reorder(ReorderStatusRequest $request, Project $project)
    {
        $this->authorize('manageStatuses', $project);

        foreach ($request->statuses as $item) {
            Status::where('id', $item['id'])
                ->where('project_id', $project->id) // safety: only update own project's statuses
                ->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true]);
    }
}
