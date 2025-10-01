<?php

namespace App\Http\Controllers\Api;

use App\Events\ProjectBroadcast;
use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\CardAttachment;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;

class AttachmentController extends Controller
{
    public function store(Request $request, Card $card)
    {
        $project = $card->boardList->board->project;
        $this->authorize('update', $project);

        $data = $request->validate([
            'file' => ['required', File::default()],
        ]);

        $file = $data['file'];
        $disk = config('filesystems.default');
        $path = $file->storePublicly('attachments', $disk);

        $attachment = $card->attachments()->create([
            'user_id' => $request->user()->id,
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
        ]);

        $attachment->load('uploader');

        ActivityLogger::for($project, $request->user())->log('card.attachment_created', $card, [
            'attachment_id' => $attachment->id,
            'name' => $attachment->original_name,
        ]);

        ProjectBroadcast::dispatch($project, 'cards.attachments', [
            'card_id' => $card->id,
            'attachment' => $attachment->toArray(),
        ]);

        return response()->json($attachment, 201);
    }

    public function destroy(Request $request, Card $card, CardAttachment $attachment)
    {
        $project = $card->boardList->board->project;
        $this->authorize('update', $project);

        if ($attachment->card_id !== $card->id) {
            abort(404);
        }

        Storage::disk($attachment->disk)->delete($attachment->path);
        $attachment->delete();

        ActivityLogger::for($project, $request->user())->log('card.attachment_deleted', $card, [
            'attachment_id' => $attachment->id,
        ]);

        ProjectBroadcast::dispatch($project, 'cards.attachments', [
            'card_id' => $card->id,
            'attachment_id' => $attachment->id,
            'deleted' => true,
        ]);

        return response()->noContent();
    }
}
