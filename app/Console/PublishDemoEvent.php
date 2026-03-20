<?php

declare(strict_types=1);

namespace App\Console;

use App\Broker\RabbitMqPublisher;
use App\Event\ActivityRecorded;
use App\Event\EventSerializer;
use Exception;
use Random\RandomException;

final readonly class PublishDemoEvent
{
    public function __construct(
        private RabbitMqPublisher $publisher,
        private EventSerializer   $serializer,
    ) {
    }

    /**
     * @throws RandomException
     * @throws \JsonException
     * @throws Exception
     */
    public function run(): void
    {
        $event = new ActivityRecorded(
            id: 'evt-' . bin2hex(random_bytes(4)),
            occurredAt: gmdate('c'),
            payload: [
                'category' => 'user-action',
                'source' => 'demo-producer',
                'subject' => 'document-42',
            ],
        );

        $payload = $this->serializer->serialize($event);
        $this->publisher->publish($payload);

        printf("[producer] published valid event=%s\n", $event->id());
    }
}
