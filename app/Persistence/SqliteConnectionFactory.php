<?php

declare(strict_types=1);

namespace App\Persistence;

use PDO;
use RuntimeException;

final readonly class SqliteConnectionFactory
{
    public function __construct(
        private SqliteConfig $config,
    ) {
    }

    public function create(): PDO
    {
        $directory = dirname($this->config->path);

        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new RuntimeException(sprintf(
                'Unable to create SQLite directory: %s',
                $directory,
            ));
        }

        $pdo = new PDO('sqlite:' . $this->config->path);

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }
}
