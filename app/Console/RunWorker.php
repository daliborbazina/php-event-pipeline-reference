<?php

declare(strict_types=1);

namespace App\Console;

use App\Broker\RabbitMqConsumer;
use App\Worker\ActivityRecordedWorker;

final readonly class RunWorker
{
    public function __construct(
        private RabbitMqConsumer       $consumer,
        private ActivityRecordedWorker $worker,
    ) {
    }

    public function run(): void
    {
        echo "[worker] waiting for messages...\n";

        $this->consumer->consume(function (string $payload): void {
            $this->worker->handle($payload);
        });
    }
}
