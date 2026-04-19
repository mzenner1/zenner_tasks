<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user();

        $base = Task::query()
            ->whereHas('assignees', fn ($q) => $q->where('user_id', $user->id))
            ->where('is_archived', false)
            ->with(['project', 'status', 'assignees']);

        $overdue = (clone $base)
            ->overdue()
            ->orderBy('due_date')
            ->get();

        $dueSoon = (clone $base)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '>=', now())
            ->whereDate('due_date', '<=', now()->addDays(7))
            ->whereHas('status', fn ($q) => $q->where('is_closed', false))
            ->orderBy('due_date')
            ->get();

        $recentlyUpdated = (clone $base)
            ->orderBy('updated_at', 'desc')
            ->limit(15)
            ->get();

        return view('dashboard', compact('overdue', 'dueSoon', 'recentlyUpdated'));
    }
}
