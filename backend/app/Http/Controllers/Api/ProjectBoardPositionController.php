<?php

namespace App\Http\Controllers\Api;

use App\Events\ProjectBroadcast;
use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectBoardPositionController extends Controller
{
    public function __invoke(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $data = $request->validate([
            'board_ids' => ['required', 'array'],
            'board_ids.*' => ['integer', 'exists:boards,id'],
        ]);

        $ids = collect($data['board_ids']);

        DB::transaction(function () use ($ids, $project) {
            $ids->values()->each(function ($id, $index) use ($project) {
                $board = Board::where('project_id', $project->id)->findOrFail($id);
                $board->update(['position' => $index + 1]);
            });
        });

        $project->load('boards');

        ProjectBroadcast::dispatch($project, 'boards.reordered', [
            'boards' => $project->boards->toArray(),
        ]);

        return response()->json([
            'boards' => $project->boards,
        ]);
    }
}
