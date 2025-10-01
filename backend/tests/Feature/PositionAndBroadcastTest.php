<?php

namespace Tests\Feature;

use App\Events\ProjectBroadcast;
use App\Models\Board;
use App\Models\BoardList;
use App\Models\Card;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PositionAndBroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_reorder_lists_and_cards_emits_events(): void
    {
        Event::fake(ProjectBroadcast::class);

        $owner = User::factory()->create();
        $project = Project::factory()->for($owner, 'owner')->create();
        $project->members()->attach($owner->id, ['role' => Project::ROLE_OWNER]);

        $board = Board::factory()->for($project)->create(['position' => 1]);

        $lists = collect(range(1, 3))->map(fn ($i) => BoardList::factory()->for($board)->create([
            'position' => $i,
            'name' => 'List '.$i,
        ]));

        $list = $lists->first();
        $cards = collect(range(1, 3))->map(fn ($i) => Card::factory()->for($list, 'boardList')->create([
            'position' => $i,
            'title' => 'Card '.$i,
        ]));

        Sanctum::actingAs($owner);

        $this->postJson("/api/boards/{$board->id}/lists/reorder", [
            'list_ids' => $lists->pluck('id')->reverse()->all(),
        ])->assertOk();

        $this->assertEquals(1, $lists->last()->fresh()->position);
        $this->assertEquals(3, $lists->first()->fresh()->position);

        $this->postJson("/api/lists/{$list->id}/cards/reorder", [
            'card_ids' => $cards->pluck('id')->reverse()->all(),
        ])->assertOk();

        $this->assertEquals(1, $cards->last()->fresh()->position);
        $this->assertEquals(3, $cards->first()->fresh()->position);

        Event::assertDispatched(ProjectBroadcast::class);
    }
}
