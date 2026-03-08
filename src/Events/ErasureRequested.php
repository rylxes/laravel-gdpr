<?php

namespace Rylxes\Gdpr\Events;

use DateTimeInterface;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ErasureRequested
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $erasureRequestId,
        public readonly int|string $userId,
        public readonly string $strategy,
        public readonly DateTimeInterface $scheduledAt,
    ) {
    }
}
