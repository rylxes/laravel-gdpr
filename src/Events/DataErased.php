<?php

namespace Rylxes\Gdpr\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DataErased
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param array<string, int> $erasedModels Model class => count of records erased
     */
    public function __construct(
        public readonly int $erasureRequestId,
        public readonly int|string $userId,
        public readonly string $strategy,
        public readonly array $erasedModels,
    ) {
    }
}
