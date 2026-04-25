<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskWatcherController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $task->addWatcher(auth()->id());

        return back()->with('success', 'You are now watching this task.');
    }

    public function destroy(Request $request, Task $task)
    {
        $task->watchers()->detach(auth()->id());

        return back()->with('success', 'You have unwatched this task.');
    }
}
