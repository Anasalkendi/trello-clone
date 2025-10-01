<?php

namespace App\Http\Controllers\Api;

use App\Events\ProjectBroadcast;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectMemberController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $this->authorize('manageMembers', $project);

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['required', Rule::in(Project::roles())],
        ]);

        if ($project->members()->where('user_id', $data['user_id'])->exists()) {
            return response()->json(['message' => 'User is already a member of this project.'], 422);
        }

        $project->members()->attach($data['user_id'], ['role' => $data['role']]);

        $member = User::find($data['user_id']);

        ActivityLogger::for($project, $request->user())->log('project.member_added', $project, [
            'member_id' => $member->id,
            'role' => $data['role'],
        ]);

        ProjectBroadcast::dispatch($project, 'members.updated', [
            'members' => $project->members()->withPivot('role')->get()->toArray(),
        ]);

        return response()->json($member, 201);
    }

    public function update(Request $request, Project $project, User $user)
    {
        $this->authorize('manageMembers', $project);

        $data = $request->validate([
            'role' => ['required', Rule::in(Project::roles())],
        ]);

        if ($project->owner_id === $user->id) {
            return response()->json(['message' => 'Cannot change the owner role.'], 422);
        }

        $project->members()->updateExistingPivot($user->id, ['role' => $data['role']]);

        ActivityLogger::for($project, $request->user())->log('project.member_updated', $project, [
            'member_id' => $user->id,
            'role' => $data['role'],
        ]);

        ProjectBroadcast::dispatch($project, 'members.updated', [
            'members' => $project->members()->withPivot('role')->get()->toArray(),
        ]);

        return response()->json(['message' => 'Role updated.']);
    }

    public function destroy(Request $request, Project $project, User $user)
    {
        $this->authorize('manageMembers', $project);

        if ($project->owner_id === $user->id) {
            return response()->json(['message' => 'Cannot remove the project owner.'], 422);
        }

        $project->members()->detach($user->id);

        ActivityLogger::for($project, $request->user())->log('project.member_removed', $project, [
            'member_id' => $user->id,
        ]);

        ProjectBroadcast::dispatch($project, 'members.updated', [
            'members' => $project->members()->withPivot('role')->get()->toArray(),
        ]);

        return response()->noContent();
    }
}
