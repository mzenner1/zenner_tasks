<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProjectMember
{
    public function handle(Request $request, Closure $next): Response
    {
        $project = $request->route('project');
        $user    = auth()->user();

        if (!$project) {
            return $next($request);
        }

        if ($user->role === 'super_admin') {
            return $next($request);
        }

        if (!$project->members->contains($user)) {
            abort(403, 'You are not a member of this project.');
        }

        return $next($request);
    }
}
