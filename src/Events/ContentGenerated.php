<?php

namespace Inovector\Mixpost\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContentGenerated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public array $content,
        public int $organizationId
    ) {}

    public function broadcastOn()
    {
        return new Channel('organization.' . $this->organizationId);
    }

    public function broadcastAs()
    {
        return 'content.generated';
    }
} 