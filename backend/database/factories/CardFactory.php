<?php

namespace Database\Factories;

use App\Models\BoardList;
use App\Models\Card;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Card>
 */
class CardFactory extends Factory
{
    protected $model = Card::class;

    public function definition(): array
    {
        return [
            'board_list_id' => BoardList::factory(),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->optional()->paragraph(),
            'position' => $this->faker->numberBetween(1, 20),
            'due_at' => $this->faker->optional()->dateTimeBetween('+1 day', '+1 month'),
        ];
    }
}
