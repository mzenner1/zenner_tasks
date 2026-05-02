<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;

class ProjectController extends Controller
{
    public function archived()
    {
        $projects = Project::where('is_archived', true)
            ->withCount('tasks')
            ->with(['members', 'creator'])
            ->orderBy('name')
            ->get();

        return view('admin.projects.archived', compact('projects'));
    }

    public function unarchive(Project $project)
    {
        $project->update(['is_archived' => false]);

        return redirect()->route('admin.projects.archived')
            ->with('success', "'{$project->name}' has been unarchived.");
    }
}
