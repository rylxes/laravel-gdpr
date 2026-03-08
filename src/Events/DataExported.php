<?php

namespace Rylxes\Gdpr\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DataExported
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $exportId,
        public readonly int|string $userId,
        public readonly string $format,
        public readonly string $filePath,
    ) {
    }
}
