<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectActivityController extends Controller
{
    public function __invoke(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        $activities = $project->activities()
            ->with('user')
            ->latest()
            ->paginate(perPage: (int) $request->input('per_page', 20));

        return $activities;
    }
}
