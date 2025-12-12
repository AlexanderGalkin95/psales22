<?php

namespace App\Events;

use App\Models\GoogleExtension;
use App\Models\Project;
use App\Repositories\Facades\ProjectRepository;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class ProjectCreatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ?object $project = null;

    public array $users = [];

    private array $channels = [];

    public string $type = 'project-created';

    public string $text = '';

    /**
     * Create a new event instance.
     *
     */
    public function __construct($projectId)
    {
        $users = ProjectRepository::findById(
            $projectId,
            ['senior_id', 'pm_id', 'id'],
            ['assessors' => fn ($q) => $q->select('user_id')]
        )->toArray();
        $this->users = array_values(Arr::dot(Arr::except($users, 'id')));

        $extensions = GoogleExtension::whereIn('user_id', $this->users)
            ->get('extension_id');
        $this->project = Project::projectForExtension($projectId);
        if (!$extensions->isEmpty()) {
            foreach ($extensions as $extension) {
                $this->channels[] = 'extension.' . $extension->extension_id;
            }
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return $this->channels;
    }

    public function broadcastAs(): string
    {
        return 'project-created';
    }

    public function broadcastWith(): array
    {
        return [
            'project' => $this->project,
        ];
    }
}
