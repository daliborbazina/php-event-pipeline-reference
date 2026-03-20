<?php

declare(strict_types=1);

namespace App\Event;

interface DomainEvent
{
    public function id(): string;

    public function type(): string;

    public function occurredAt(): string;

    /**
     * Returns event-specific payload data.
     *
     * @return array<string, mixed>
     */
    public function payload(): array;
}
