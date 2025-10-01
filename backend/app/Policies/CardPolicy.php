<?php

namespace App\Policies;

use App\Models\Card;
use App\Models\User;

class CardPolicy
{
    protected ProjectPolicy $projectPolicy;

    public function __construct()
    {
        $this->projectPolicy = new ProjectPolicy();
    }

    public function view(User $user, Card $card): bool
    {
        return $this->projectPolicy->view($user, $card->boardList->board->project);
    }

    public function update(User $user, Card $card): bool
    {
        return $this->projectPolicy->update($user, $card->boardList->board->project);
    }

    public function delete(User $user, Card $card): bool
    {
        return $this->projectPolicy->update($user, $card->boardList->board->project);
    }
}
