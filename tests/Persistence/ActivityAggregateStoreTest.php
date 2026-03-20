<?php

declare(strict_types=1);

namespace App\Tests;

use App\Persistence\ActivityAggregateSchema;
use App\Persistence\ActivityAggregateStore;
use App\Persistence\SqliteConfig;
use App\Persistence\SqliteConnectionFactory;
use PHPUnit\Framework\TestCase;

final class ActivityAggregateStoreTest extends TestCase
{
    private string $dbPath;

    protected function setUp(): void
    {
        $this->dbPath = sys_get_temp_dir() . '/test.sqlite';

        @unlink($this->dbPath);

        $config = new SqliteConfig($this->dbPath);
        $factory = new SqliteConnectionFactory($config);

        $schema = new ActivityAggregateSchema($factory);
        $schema->createIfNotExists();
    }

    public function test_increment_creates_new_row(): void
    {
        $config = new SqliteConfig($this->dbPath);
        $factory = new SqliteConnectionFactory($config);

        $store = new ActivityAggregateStore($factory);

        $store->increment(
            'user-action',
            'test-source',
            'evt-1',
            date(DATE_ATOM)
        );

        $rows = $store->all();

        $this->assertCount(1, $rows);
        $this->assertSame('user-action', $rows[0]['category']);
        $this->assertSame('test-source', $rows[0]['source']);
        $this->assertSame(1, $rows[0]['total_count']);
    }

    public function test_increment_updates_existing_row(): void
    {
        $config = new SqliteConfig($this->dbPath);
        $factory = new SqliteConnectionFactory($config);

        $store = new ActivityAggregateStore($factory);

        $store->increment('user-action', 'test-source', 'evt-1', date(DATE_ATOM));
        $store->increment('user-action', 'test-source', 'evt-2', date(DATE_ATOM));

        $rows = $store->all();

        $this->assertCount(1, $rows);
        $this->assertSame(2, $rows[0]['total_count']);
        $this->assertSame('evt-2', $rows[0]['last_event_id']);
    }
}
