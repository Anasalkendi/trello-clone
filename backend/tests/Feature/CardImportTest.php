<?php

namespace Tests\Feature;

use App\Events\ProjectBroadcast;
use App\Models\Board;
use App\Models\BoardList;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CardImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_import_cards_from_csv(): void
    {
        Event::fake(ProjectBroadcast::class);

        $owner = User::factory()->create();
        $member = User::factory()->create(['email' => 'member@example.com']);

        $project = Project::factory()->for($owner, 'owner')->create();
        $project->members()->attach($owner->id, ['role' => Project::ROLE_OWNER]);
        $project->members()->attach($member->id, ['role' => Project::ROLE_MEMBER]);

        $board = Board::factory()->for($project)->create();
        $list = BoardList::factory()->for($board)->create();

        Sanctum::actingAs($owner);

        $csv = "title,description,due_at,assignees\n".
            "Kickoff,Schedule kickoff meeting,2025-01-01,member@example.com";

        $file = UploadedFile::fake()->createWithContent('cards.csv', $csv);

        $response = $this->postJson("/api/lists/{$list->id}/cards/import", [
            'file' => $file,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('cards', [
            'board_list_id' => $list->id,
            'title' => 'Kickoff',
        ]);

        Event::assertDispatched(ProjectBroadcast::class);
    }
}
