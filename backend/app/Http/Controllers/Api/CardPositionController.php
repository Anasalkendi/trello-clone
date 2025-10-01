<?php

namespace App\Http\Controllers\Api;

use App\Events\ProjectBroadcast;
use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\BoardList;
use App\Models\Card;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CardPositionController extends Controller
{
    public function reorderLists(Request $request, Board $board)
    {
        $project = $board->project;
        $this->authorize('update', $project);

        $data = $request->validate([
            'list_ids' => ['required', 'array'],
            'list_ids.*' => ['integer', 'exists:board_lists,id'],
        ]);

        DB::transaction(function () use ($data, $board) {
            foreach (array_values($data['list_ids']) as $index => $id) {
                $list = $board->lists()->findOrFail($id);
                $list->update(['position' => $index + 1]);
            }
        });

        $board->load('lists');

        ActivityLogger::for($project, $request->user())->log('list.reordered', $board, [
            'list_ids' => $data['list_ids'],
        ]);

        ProjectBroadcast::dispatch($project, 'lists.reordered', [
            'lists' => $board->lists->toArray(),
        ]);

        return response()->json([
            'lists' => $board->lists,
        ]);
    }

    public function reorderCards(Request $request, BoardList $boardList)
    {
        $project = $boardList->board->project;
        $this->authorize('update', $project);

        $data = $request->validate([
            'card_ids' => ['required', 'array'],
            'card_ids.*' => ['integer', 'exists:cards,id'],
            'moved_card_id' => ['nullable', 'integer', 'exists:cards,id'],
        ]);

        DB::transaction(function () use ($data, $boardList, $project) {
            foreach (array_values($data['card_ids']) as $index => $id) {
                /** @var Card $card */
                $card = Card::findOrFail($id);

                if ($card->boardList->board->project_id !== $project->id) {
                    abort(422, 'Card does not belong to this project.');
                }

                if ($card->board_list_id !== $boardList->id) {
                    $card->board_list_id = $boardList->id;
                }

                $card->position = $index + 1;
                $card->save();
            }
        });

        $boardList->load('cards');

        ActivityLogger::for($project, $request->user())->log('card.reordered', $boardList, [
            'card_ids' => $data['card_ids'],
        ]);

        ProjectBroadcast::dispatch($project, 'cards.reordered', [
            'list_id' => $boardList->id,
            'cards' => $boardList->cards->toArray(),
        ]);

        return response()->json([
            'cards' => $boardList->cards,
        ]);
    }
}
