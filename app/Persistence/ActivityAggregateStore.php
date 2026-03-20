<?php

declare(strict_types=1);

namespace App\Persistence;

use PDO;

final readonly class ActivityAggregateStore
{
    public function __construct(
        private SqliteConnectionFactory $connectionFactory,
    ) {
    }

    public function increment(string $category, string $source, string $eventId, string $updatedAt): void
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO activity_aggregate (
                category,
                source,
                total_count,
                last_event_id,
                updated_at
            ) VALUES (
                :category,
                :source,
                1,
                :last_event_id,
                :updated_at
            )
            ON CONFLICT(category, source) DO UPDATE SET
                total_count = total_count + 1,
                last_event_id = excluded.last_event_id,
                updated_at = excluded.updated_at'
        );

        if ($statement === false) {
            throw new \RuntimeException('Failed to prepare increment statement.');
        }

        $statement->execute([
            'category' => $category,
            'source' => $source,
            'last_event_id' => $eventId,
            'updated_at' => $updatedAt,
        ]);
    }

    /**
     * @return list<array{
     *     category: string,
     *     source: string,
     *     total_count: int,
     *     last_event_id: string,
     *     updated_at: string
     * }>
     */
    public function all(): array
    {
        $statement = $this->pdo()->query(
            'SELECT category, source, total_count, last_event_id, updated_at
             FROM activity_aggregate
             ORDER BY category, source'
        );

        // PHPStan still sees PDO::query() as returning PDOStatement|false,
        // even though the connection uses ERRMODE_EXCEPTION: `$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION)`.
        if ($statement === false) {
            throw new \RuntimeException('Failed to execute query.');
        }

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function pdo(): PDO
    {
        return $this->connectionFactory->create();
    }
}
