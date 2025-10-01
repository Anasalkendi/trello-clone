<?php

namespace App\Http\Controllers\Api;

use App\Events\ProjectBroadcast;
use App\Http\Controllers\Controller;
use App\Models\BoardList;
use App\Models\Card;
use App\Models\Project;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CardController extends Controller
{
    public function index(BoardList $boardList)
    {
        $project = $this->resolveProject($boardList);
        $this->authorize('view', $project);

        return $boardList->cards()->with(['assignees', 'attachments'])->orderBy('position')->get();
    }

    public function store(Request $request, BoardList $boardList)
    {
        $project = $this->resolveProject($boardList);
        $this->authorize('update', $project);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'assignee_ids' => ['nullable', 'array'],
            'assignee_ids.*' => ['integer', Rule::exists('project_user', 'user_id')->where(fn ($q) => $q->where('project_id', $project->id))],
        ]);

        $position = (int) $boardList->cards()->max('position') + 1;

        $card = $boardList->cards()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'due_at' => $data['due_at'] ?? null,
            'position' => $position,
        ]);

        if (! empty($data['assignee_ids'])) {
            $card->assignees()->sync($data['assignee_ids']);
        }

        $card->load('assignees', 'attachments');

        ActivityLogger::for($project, $request->user())->log('card.created', $card, [
            'card_id' => $card->id,
        ]);

        ProjectBroadcast::dispatch($project, 'cards.updated', [
            'card' => $card->toArray(),
        ]);

        return response()->json($card, 201);
    }

    public function show(BoardList $boardList, Card $card)
    {
        $project = $this->resolveProject($boardList);
        $this->authorize('view', $project);
        $this->ensureCardBelongsToList($boardList, $card);

        return $card->load('assignees', 'attachments');
    }

    public function update(Request $request, BoardList $boardList, Card $card)
    {
        $project = $this->resolveProject($boardList);
        $this->authorize('update', $project);
        $this->ensureCardBelongsToList($boardList, $card);

        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'archived' => ['nullable', 'boolean'],
            'assignee_ids' => ['nullable', 'array'],
            'assignee_ids.*' => ['integer', Rule::exists('project_user', 'user_id')->where(fn ($q) => $q->where('project_id', $project->id))],
            'board_list_id' => ['sometimes', 'integer', Rule::exists('board_lists', 'id')],
        ]);

        DB::transaction(function () use ($data, $card, $project) {
            if (! empty($data['board_list_id']) && $data['board_list_id'] !== $card->board_list_id) {
                $newList = BoardList::findOrFail($data['board_list_id']);

                if ($newList->board->project_id !== $project->id) {
                    abort(422, 'Cannot move card to a list outside of the project.');
                }

                $card->update([
                    'board_list_id' => $newList->id,
                    'position' => (int) $newList->cards()->max('position') + 1,
                ]);
            }

            $card->update(collect($data)->except(['assignee_ids', 'board_list_id'])->toArray());

            if (array_key_exists('assignee_ids', $data)) {
                $card->assignees()->sync($data['assignee_ids'] ?? []);
            }
        });

        $card->refresh()->load('assignees', 'attachments');

        ActivityLogger::for($project, $request->user())->log('card.updated', $card, $data);

        ProjectBroadcast::dispatch($project, 'cards.updated', [
            'card' => $card->toArray(),
        ]);

        return $card;
    }

    public function destroy(Request $request, BoardList $boardList, Card $card)
    {
        $project = $boardList->board->project;
        $this->authorize('update', $project);
        $this->ensureCardBelongsToList($boardList, $card);

        $card->delete();

        ActivityLogger::for($project, $request->user())->log('card.deleted', $card, [
            'card_id' => $card->id,
        ]);

        ProjectBroadcast::dispatch($project, 'cards.deleted', [
            'card_id' => $card->id,
        ]);

        return response()->noContent();
    }

    protected function ensureCardBelongsToList(BoardList $boardList, Card $card): void
    {
        if ($card->board_list_id !== $boardList->id) {
            abort(404);
        }
    }

    protected function resolveProject(BoardList $boardList): Project
    {
        $projectId = $boardList->project_id;

        if (! $projectId) {
            $projectId = DB::table('boards')->where('id', $boardList->board_id)->value('project_id');
        }

        if (! $projectId) {
            abort(404, 'Board not found for list');
        }

        return Project::query()->findOrFail($projectId);
    }
}
