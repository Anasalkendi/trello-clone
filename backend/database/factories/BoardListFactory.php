<?php

namespace Database\Factories;

use App\Models\Board;
use App\Models\BoardList;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BoardList>
 */
class BoardListFactory extends Factory
{
    protected $model = BoardList::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'board_id' => Board::factory(),
            'name' => $this->faker->unique()->words(2, true),
            'position' => $this->faker->numberBetween(1, 10),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (BoardList $list) {
            if (! $list->project_id && $list->board) {
                $list->project_id = $list->board->project_id;
            }
        })->afterCreating(function (BoardList $list) {
            if (! $list->project_id && $list->board) {
                $list->update(['project_id' => $list->board->project_id]);
            }
        });
    }
}
