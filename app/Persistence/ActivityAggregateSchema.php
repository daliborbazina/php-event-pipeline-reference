<?php

declare(strict_types=1);

namespace App\Persistence;

final readonly class ActivityAggregateSchema
{
    public function __construct(
        private SqliteConnectionFactory $connectionFactory,
    ) {
    }

    public function createIfNotExists(): void
    {
        $pdo = $this->connectionFactory->create();

        // Schema creation is kept explicit so the example can run without a migration system.
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS activity_aggregate (
                category TEXT NOT NULL,
                source TEXT NOT NULL,
                total_count INTEGER NOT NULL,
                last_event_id TEXT NOT NULL,
                updated_at TEXT NOT NULL,
                PRIMARY KEY (category, source)
            )'
        );
    }
}
