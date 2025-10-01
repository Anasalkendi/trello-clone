<?php

namespace App\Http\Controllers\Api;

use App\Events\ProjectBroadcast;
use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\BoardList;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class BoardListController extends Controller
{
    public function index(Board $board)
    {
        $project = $board->project;
        $this->authorize('view', $project);

        return $board->lists()->with('cards.assignees')->orderBy('position')->get();
    }

    public function store(Request $request, Board $board)
    {
        $project = $board->project;
        $this->authorize('update', $project);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $position = (int) $board->lists()->max('position') + 1;

        $list = $board->lists()->create([
            'project_id' => $project->id,
            'name' => $data['name'],
            'position' => $position,
        ]);

        ActivityLogger::for($project, $request->user())->log('list.created', $list, [
            'list_id' => $list->id,
        ]);

        ProjectBroadcast::dispatch($project, 'lists.updated', [
            'list' => $list->toArray(),
        ]);

        return response()->json($list->load('cards'), 201);
    }

    public function show(Board $board, BoardList $boardList)
    {
        $project = $board->project;
        $this->authorize('view', $project);
        $this->ensureListBelongsToBoard($board, $boardList);

        return $boardList->load('cards.assignees');
    }

    public function update(Request $request, Board $board, BoardList $boardList)
    {
        $project = $board->project;
        $this->authorize('update', $project);
        $this->ensureListBelongsToBoard($board, $boardList);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
        ]);

        $boardList->update($data);

        ActivityLogger::for($project, $request->user())->log('list.updated', $boardList, $data);

        ProjectBroadcast::dispatch($project, 'lists.updated', [
            'list' => $boardList->toArray(),
        ]);

        return $boardList->fresh('cards');
    }

    public function destroy(Request $request, Board $board, BoardList $boardList)
    {
        $project = $board->project;
        $this->authorize('update', $project);
        $this->ensureListBelongsToBoard($board, $boardList);

        $boardList->delete();

        ActivityLogger::for($project, $request->user())->log('list.deleted', $boardList, [
            'list_id' => $boardList->id,
        ]);

        ProjectBroadcast::dispatch($project, 'lists.deleted', [
            'list_id' => $boardList->id,
        ]);

        return response()->noContent();
    }

    protected function ensureListBelongsToBoard(Board $board, BoardList $boardList): void
    {
        if ($boardList->board_id !== $board->id) {
            abort(404);
        }
    }
}
