<?php

declare(strict_types=1);

namespace App\Worker;

use App\Event\EventDeserializer;
use App\Persistence\ActivityAggregateStore;
use Throwable;

final readonly class ActivityRecordedWorker
{
    public function __construct(
        private EventDeserializer      $deserializer,
        private ActivityAggregateStore $aggregateStore,
    ) {
    }

    public function handle(string $payload): void
    {
        try {
            $event = $this->deserializer->deserialize($payload);

            $data = $event->payload();

            $this->aggregateStore->increment(
                $data['category'],
                $data['source'],
                $event->id(),
                $event->occurredAt(),
            );

            printf(
                "[worker] processed event=%s type=%s category=%s source=%s\n",
                $event->id(),
                $event->type(),
                $data['category'],
                $data['source'],
            );
        } catch (Throwable $exception) {
            printf(
                "[worker] rejected payload reason=%s\n",
                $exception->getMessage(),
            );
        }
    }
}
