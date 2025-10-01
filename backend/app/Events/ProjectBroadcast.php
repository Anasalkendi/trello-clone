<?php

namespace App\Events;

use App\Models\Project;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectBroadcast implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public Project $project,
        public string $type,
        public array $payload
    ) {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('projects.'.$this->project->getKey());
    }

    public function broadcastAs(): string
    {
        return 'project.'.$this->type;
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
