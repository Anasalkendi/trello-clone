<?php

namespace Tests\Feature;

use App\Events\ProjectBroadcast;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_project(): void
    {
        Sanctum::actingAs(User::factory()->create());

        Event::fake(ProjectBroadcast::class);

        $response = $this->postJson('/api/projects', [
            'name' => 'My Demo Project',
            'description' => 'A test project',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('projects', [
            'name' => 'My Demo Project',
        ]);

        Event::assertDispatched(ProjectBroadcast::class);
    }

    public function test_viewer_cannot_update_project(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();

        $project = Project::factory()->for($owner, 'owner')->create();
        $project->members()->attach($owner->id, ['role' => Project::ROLE_OWNER]);
        $project->members()->attach($viewer->id, ['role' => Project::ROLE_VIEWER]);

        Sanctum::actingAs($viewer);

        $this->putJson('/api/projects/'.$project->id, [
            'name' => 'Unauthorized Update',
        ])->assertForbidden();
    }

    public function test_admin_can_update_project(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();

        $project = Project::factory()->for($owner, 'owner')->create();
        $project->members()->attach($owner->id, ['role' => Project::ROLE_OWNER]);
        $project->members()->attach($admin->id, ['role' => Project::ROLE_ADMIN]);

        Sanctum::actingAs($admin);

        $response = $this->putJson('/api/projects/'.$project->id, [
            'name' => 'Updated Name',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => 'Updated Name']);
    }
}
