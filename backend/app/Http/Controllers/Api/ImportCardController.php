<?php

namespace App\Http\Controllers\Api;

use App\Events\ProjectBroadcast;
use App\Http\Controllers\Controller;
use App\Models\BoardList;
use App\Models\Card;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\File;

class ImportCardController extends Controller
{
    public function __invoke(Request $request, BoardList $boardList)
    {
        $project = $boardList->board->project;
        $this->authorize('update', $project);

        $data = $request->validate([
            'file' => ['required', File::types(['text/csv', 'csv', 'txt'])->max(10 * 1024)],
        ]);

        $file = $data['file'];

        $rows = $this->parseCsv($file->getRealPath());

        $created = [];

        DB::transaction(function () use ($rows, $boardList, $project, $request, &$created) {
            $position = (int) $boardList->cards()->max('position');
            $membersByEmail = $project->members()->pluck('users.id', 'users.email')->mapWithKeys(fn ($id, $email) => [strtolower($email) => $id]);

            foreach ($rows as $row) {
                if (empty($row['title'])) {
                    continue;
                }

                $position++;

                $card = $boardList->cards()->create([
                    'title' => $row['title'],
                    'description' => $row['description'] ?? null,
                    'due_at' => isset($row['due_at']) && $row['due_at'] !== '' ? Carbon::parse($row['due_at']) : null,
                    'position' => $position,
                ]);

                if (! empty($row['assignees'])) {
                    $emails = collect(preg_split('/[;,]/', $row['assignees']))
                        ->map(fn ($email) => strtolower(trim($email)))
                        ->filter();

                    $card->assignees()->syncWithoutDetaching(
                        $emails->map(fn ($email) => $membersByEmail[$email] ?? null)->filter()->all()
                    );
                }

                $created[] = $card->load('assignees');

                ActivityLogger::for($project, $request->user())->log('card.imported', $card, [
                    'card_id' => $card->id,
                    'title' => $card->title,
                ]);
            }
        });

        if (! empty($created)) {
            ProjectBroadcast::dispatch($project, 'cards.imported', [
                'list_id' => $boardList->id,
                'cards' => collect($created)->map->toArray()->all(),
            ]);
        }

        return response()->json([
            'created' => $created,
        ]);
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    protected function parseCsv(string $path): array
    {
        $rows = [];
        if (($handle = fopen($path, 'rb')) === false) {
            return $rows;
        }

        $header = null;
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            if (! $header) {
                $header = array_map(fn ($value) => strtolower(trim($value)), $data);
                continue;
            }

            $row = [];
            foreach ($header as $index => $column) {
                $row[$column] = $data[$index] ?? null;
            }

            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }
}
