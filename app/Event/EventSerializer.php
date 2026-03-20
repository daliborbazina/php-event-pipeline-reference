<?php

declare(strict_types=1);

namespace App\Event;

final class EventSerializer
{
    /**
     * @throws \JsonException
     */
    public function serialize(DomainEvent $event): string
    {
        return json_encode([
            'id' => $event->id(),
            'type' => $event->type(),
            'occurred_at' => $event->occurredAt(),
            'payload' => $event->payload(),
        ], JSON_THROW_ON_ERROR);
    }
}
