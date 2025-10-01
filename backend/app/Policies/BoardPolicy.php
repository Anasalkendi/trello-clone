<?php

namespace App\Policies;

use App\Models\Board;
use App\Models\Project;
use App\Models\User;

class BoardPolicy
{
    protected ProjectPolicy $projectPolicy;

    public function __construct()
    {
        $this->projectPolicy = new ProjectPolicy();
    }

    public function view(User $user, Board $board): bool
    {
        return $this->projectPolicy->view($user, $board->project);
    }

    public function create(User $user, Project $project): bool
    {
        return $this->projectPolicy->update($user, $project);
    }

    public function update(User $user, Board $board): bool
    {
        return $this->projectPolicy->update($user, $board->project);
    }

    public function delete(User $user, Board $board): bool
    {
        return $this->projectPolicy->update($user, $board->project);
    }
}
