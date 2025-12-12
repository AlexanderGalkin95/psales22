<?php

namespace App\Events;

use App\Models\GoogleExtension;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class GoogleExtensionEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ?string $extensionId;

    /**
     * Create a new event instance.
     *
     * @param string $extension_id
     */
    public function __construct(string $extension_id)
    {
        $this->extensionId = $extension_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return ['extension.' . $this->extensionId];
    }

    public function broadcastAs(): string
    {
        return 'extension-registered';
    }

    public function broadcastWith(): array
    {
        return [
            'extension' => $this->extensionId,
        ];
    }
}
