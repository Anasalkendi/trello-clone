<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\BoardList;
use App\Models\Card;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::firstOrCreate([
            'email' => 'owner@example.com',
        ], [
            'name' => 'Demo Owner',
            'password' => Hash::make('password'),
        ]);

        $member = User::firstOrCreate([
            'email' => 'member@example.com',
        ], [
            'name' => 'Demo Member',
            'password' => Hash::make('password'),
        ]);

        $viewer = User::firstOrCreate([
            'email' => 'viewer@example.com',
        ], [
            'name' => 'Demo Viewer',
            'password' => Hash::make('password'),
        ]);

        $project = Project::firstOrCreate([
            'slug' => 'demo-workspace',
        ], [
            'name' => 'Demo Workspace',
            'description' => 'Sample workspace showcasing a kanban board.',
            'owner_id' => $owner->id,
        ]);

        $project->members()->syncWithoutDetaching([
            $owner->id => ['role' => Project::ROLE_OWNER],
            $member->id => ['role' => Project::ROLE_MEMBER],
            $viewer->id => ['role' => Project::ROLE_VIEWER],
        ]);

        $boards = [
            ['name' => 'Demo Project Board', 'description' => 'Main kanban board for the demo project.'],
        ];

        foreach ($boards as $index => $data) {
            $board = $project->boards()->firstOrCreate([
                'name' => $data['name'],
            ], [
                'description' => $data['description'],
                'position' => $index + 1,
            ]);

            $lists = [
                ['name' => 'Ideas'],
                ['name' => 'In Progress'],
                ['name' => 'Review'],
                ['name' => 'Done'],
            ];

            foreach ($lists as $listIndex => $listData) {
                $list = $board->lists()->firstOrCreate([
                    'name' => $listData['name'],
                ], [
                    'project_id' => $project->id,
                    'position' => $listIndex + 1,
                ]);

                if ($list->wasRecentlyCreated) {
                    $cards = match ($listData['name']) {
                        'Ideas' => [
                            ['title' => 'Capture feedback from beta testers', 'description' => 'Aggregate notes from the last feedback session.'],
                            ['title' => 'Plan marketing launch', 'description' => 'Outline the rollout checklist for the next release.'],
                        ],
                        'In Progress' => [
                            ['title' => 'Design sprint planning', 'description' => 'Create wireframes for the new dashboard.'],
                        ],
                        'Review' => [
                            ['title' => 'QA for attachment uploads', 'description' => 'Verify limits and error messaging.'],
                        ],
                        'Done' => [
                            ['title' => 'Set up Sanctum authentication', 'description' => 'API token support implemented.'],
                        ],
                        default => [],
                    };

                    foreach ($cards as $cardIndex => $cardData) {
                        $card = $list->cards()->create([
                            'title' => $cardData['title'],
                            'description' => $cardData['description'],
                            'position' => $cardIndex + 1,
                        ]);

                        $card->assignees()->syncWithoutDetaching([$member->id]);
                    }
                }
            }
        }
    }
}
