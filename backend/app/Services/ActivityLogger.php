<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public function __construct(
        protected Project $project,
        protected ?User $user
    ) {
    }

    public static function for(Project $project, ?User $user = null): self
    {
        return new self($project, $user);
    }

    public function log(string $event, ?Model $subject = null, array $properties = []): Activity
    {
        return Activity::create([
            'project_id' => $this->project->id,
            'user_id' => $this->user?->id,
            'event' => $event,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'properties' => $properties,
        ]);
    }
}
