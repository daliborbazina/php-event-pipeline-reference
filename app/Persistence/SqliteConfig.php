<?php

declare(strict_types=1);

namespace App\Persistence;

final readonly class SqliteConfig
{
    public function __construct(
        public string $path
    ) {
        if ($this->path === '') {
            throw new \InvalidArgumentException('SQLite path must not be empty.');
        }
    }

    public static function fromEnv(): self
    {
        return new self(
            path: self::env('SQLITE_PATH', '/storage/sqlite/events.sqlite'),
        );
    }

    private static function env(string $key, string $default): string
    {
        $value = getenv($key);

        return $value === false ? $default : $value;
    }
}
