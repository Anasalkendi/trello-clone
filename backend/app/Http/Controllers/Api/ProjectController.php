<?php

namespace App\Http\Controllers\Api;

use App\Events\ProjectBroadcast;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::with([
            'owner',
            'members',
            'boards.lists.cards.assignees',
        ])->visibleTo($request->user())->get();

        return $projects;
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $project = Project::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'owner_id' => $user->id,
            'slug' => $this->generateSlug($data['name']),
        ]);

        $project->members()->attach($user->id, ['role' => Project::ROLE_OWNER]);

        $project->load('owner', 'members', 'boards.lists.cards');

        ActivityLogger::for($project, $user)->log('project.created', $project, [
            'name' => $project->name,
        ]);

        ProjectBroadcast::dispatch($project, 'updated', [
            'project' => $project->toArray(),
        ]);

        return response()->json($project, 201);
    }

    public function show(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        return $project->load([
            'owner',
            'members',
            'boards.lists.cards.assignees',
            'boards.lists.cards.attachments',
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('projects')->ignore($project->id)],
        ]);

        if (array_key_exists('name', $data) && ! array_key_exists('slug', $data)) {
            $data['slug'] = $this->generateSlug($data['name'], $project->id);
        }

        $project->update($data);

        $project->refresh()->load('owner', 'members');

        ActivityLogger::for($project, $request->user())->log('project.updated', $project, $data);

        ProjectBroadcast::dispatch($project, 'updated', [
            'project' => $project->toArray(),
        ]);

        return $project;
    }

    public function destroy(Request $request, Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();

        ActivityLogger::for($project, $request->user())->log('project.deleted', $project, [
            'name' => $project->name,
        ]);

        ProjectBroadcast::dispatch($project, 'deleted', [
            'project_id' => $project->id,
        ]);

        return response()->noContent();
    }

    protected function generateSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $i = 1;

        while (Project::where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $original.'-'.$i++;
        }

        return $slug;
    }
}
