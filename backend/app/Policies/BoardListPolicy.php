<?php

namespace App\Policies;

use App\Models\BoardList;
use App\Models\User;

class BoardListPolicy
{
    protected ProjectPolicy $projectPolicy;

    public function __construct()
    {
        $this->projectPolicy = new ProjectPolicy();
    }

    public function view(User $user, BoardList $list): bool
    {
        return $this->projectPolicy->view($user, $list->board->project);
    }

    public function update(User $user, BoardList $list): bool
    {
        return $this->projectPolicy->update($user, $list->board->project);
    }

    public function delete(User $user, BoardList $list): bool
    {
        return $this->projectPolicy->update($user, $list->board->project);
    }
}
