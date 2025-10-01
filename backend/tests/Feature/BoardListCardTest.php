<?php

namespace Tests\Feature;

use App\Events\ProjectBroadcast;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BoardListCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_board_list_and_card(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->for($owner, 'owner')->create();
        $project->members()->attach($owner->id, ['role' => Project::ROLE_OWNER]);

        Sanctum::actingAs($owner);
        Event::fake(ProjectBroadcast::class);

        $boardResponse = $this->postJson("/api/projects/{$project->id}/boards", [
            'name' => 'Sprint 1',
        ]);

        $boardResponse->assertCreated();
        $boardId = $boardResponse->json('id');
        $this->assertDatabaseHas('boards', ['id' => $boardId]);

        $listResponse = $this->postJson("/api/boards/{$boardId}/lists", [
            'name' => 'To Do',
        ]);

        $listResponse->assertCreated();
        $listId = $listResponse->json('id');
        $this->assertDatabaseHas('board_lists', ['id' => $listId]);

        $cardResponse = $this->postJson("/api/lists/{$listId}/cards", [
            'title' => 'Implement API',
            'description' => 'Build endpoints',
        ]);

        $cardResponse->assertCreated();

        $this->assertDatabaseHas('boards', ['id' => $boardId, 'name' => 'Sprint 1']);
        $this->assertDatabaseHas('board_lists', ['id' => $listId, 'name' => 'To Do']);
        $this->assertDatabaseHas('cards', ['board_list_id' => $listId, 'title' => 'Implement API']);

        Event::assertDispatched(ProjectBroadcast::class);
    }

    public function test_viewer_cannot_create_board(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $project = Project::factory()->for($owner, 'owner')->create();
        $project->members()->attach($owner->id, ['role' => Project::ROLE_OWNER]);
        $project->members()->attach($viewer->id, ['role' => Project::ROLE_VIEWER]);

        Sanctum::actingAs($viewer);

        $this->postJson("/api/projects/{$project->id}/boards", [
            'name' => 'Nope',
        ])->assertForbidden();
    }
}
