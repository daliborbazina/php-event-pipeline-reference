<?php

declare(strict_types=1);

namespace App\Console;

use App\Broker\RabbitMqPublisher;
use Exception;
use Random\RandomException;

final readonly class PublishInvalidEvent
{
    public function __construct(
        private RabbitMqPublisher $publisher,
    ) {
    }

    /**
     * @throws \JsonException
     * @throws RandomException
     * @throws Exception
     */
    public function run(): void
    {
        $payload = json_encode([
            'id' => 'evt-invalid-' . bin2hex(random_bytes(4)),
            'occurred_at' => gmdate('c'),
            'payload' => [
                'category' => 'user-action',
            ],
        ], JSON_THROW_ON_ERROR);

        $this->publisher->publish($payload);

        echo "[producer] published invalid event\n";
    }
}
