<?php

namespace App\Http\Controllers\Api;

use App\Events\ProjectBroadcast;
use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Project;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('view', $project);

        return $project->boards()->with('lists.cards.assignees')->orderBy('position')->get();
    }

    public function store(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        $position = (int) $project->boards()->max('position') + 1;

        $board = $project->boards()->create(array_merge($data, [
            'position' => $position,
        ]));

        $board->load('lists.cards');

        ActivityLogger::for($project, $request->user())->log('board.created', $board, [
            'board_id' => $board->id,
        ]);

        ProjectBroadcast::dispatch($project, 'boards.updated', [
            'board' => $board->toArray(),
        ]);

        return response()->json($board, 201);
    }

    public function show(Project $project, Board $board)
    {
        $this->authorize('view', $project);
        $this->ensureBoardBelongsToProject($project, $board);

        return $board->load('lists.cards.assignees');
    }

    public function update(Request $request, Project $project, Board $board)
    {
        $this->authorize('update', $project);
        $this->ensureBoardBelongsToProject($project, $board);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        $board->update($data);
        $board->refresh()->load('lists.cards');

        ActivityLogger::for($project, $request->user())->log('board.updated', $board, $data);

        ProjectBroadcast::dispatch($project, 'boards.updated', [
            'board' => $board->toArray(),
        ]);

        return $board;
    }

    public function destroy(Request $request, Project $project, Board $board)
    {
        $this->authorize('update', $project);
        $this->ensureBoardBelongsToProject($project, $board);

        $board->delete();

        ActivityLogger::for($project, $request->user())->log('board.deleted', $board, [
            'board_id' => $board->id,
        ]);

        ProjectBroadcast::dispatch($project, 'boards.deleted', [
            'board_id' => $board->id,
        ]);

        return response()->noContent();
    }

    protected function ensureBoardBelongsToProject(Project $project, Board $board): void
    {
        if ($board->project_id !== $project->id) {
            abort(404);
        }
    }
}
