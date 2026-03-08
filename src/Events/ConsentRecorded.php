<?php

namespace Rylxes\Gdpr\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConsentRecorded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $consentLogId,
        public readonly int|string $userId,
        public readonly string $consentType,
        public readonly string $consentVersion,
    ) {
    }
}
