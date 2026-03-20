<?php

declare(strict_types=1);

namespace App\Event;

final readonly class ActivityRecorded implements DomainEvent
{
    public const string TYPE = 'activity.recorded';

    /**
     * @param array{category:string,source:string,subject:string} $payload
     */
    public function __construct(
        private string $id,
        private string $occurredAt,
        private array  $payload,
    ) {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function type(): string
    {
        return self::TYPE;
    }

    public function occurredAt(): string
    {
        return $this->occurredAt;
    }

    /**
     * @return array{category:string,source:string,subject:string}
     */
    public function payload(): array
    {
        return $this->payload;
    }
}
