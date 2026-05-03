<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('manageTags', $project);

        $tags = $project->tags()->withCount('tasks')->get();

        return view('tags.index', compact('project', 'tags'));
    }

    public function store(Request $request, Project $project)
    {
        $this->authorize('manageTags', $project);

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:50'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $project->tags()->create($validated);

        return redirect()->route('projects.tags.index', $project)
            ->with('success', 'Tag created.');
    }

    public function update(Request $request, Project $project, Tag $tag)
    {
        $this->authorize('manageTags', $project);

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:50'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $tag->update($validated);

        return redirect()->route('projects.tags.index', $project)
            ->with('success', 'Tag updated.');
    }

    public function destroy(Project $project, Tag $tag)
    {
        $this->authorize('manageTags', $project);

        $tag->delete();

        return redirect()->route('projects.tags.index', $project)
            ->with('success', 'Tag deleted.');
    }
}
