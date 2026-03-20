<?php

declare(strict_types=1);

namespace App\Console;

use App\Persistence\ActivityAggregateStore;

final readonly class InspectAggregate
{
    public function __construct(
        private ActivityAggregateStore $store,
    ) {
    }

    public function run(): void
    {
        $rows = $this->store->all();

        if ($rows === []) {
            echo "[inspect] no aggregate rows found\n";
            return;
        }

        foreach ($rows as $row) {
            printf(
                "category=%s source=%s total_count=%d last_event_id=%s updated_at=%s\n",
                $row['category'],
                $row['source'],
                $row['total_count'],
                $row['last_event_id'],
                $row['updated_at'],
            );
        }
    }
}
