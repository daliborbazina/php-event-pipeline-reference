<?php

declare(strict_types=1);

namespace App\Broker;

use InvalidArgumentException;

final readonly class RabbitMqConfig
{
    public function __construct(
        public string $host,
        public int $port,
        public string $user,
        public string $pass,
        public string $queue,
    ) {
        if ($this->host === '') {
            throw new InvalidArgumentException('RabbitMQ host must not be empty.');
        }

        if ($this->port <= 0) {
            throw new InvalidArgumentException('RabbitMQ port must be greater than 0.');
        }

        if ($this->user === '') {
            throw new InvalidArgumentException('RabbitMQ user must not be empty.');
        }

        if ($this->queue === '') {
            throw new InvalidArgumentException('RabbitMQ queue must not be empty.');
        }
    }

    public static function fromEnv(): self
    {
        return new self(
            host: self::env('RABBITMQ_HOST', 'rabbitmq'),
            port: self::envInt('RABBITMQ_PORT', 5672),
            user: self::env('RABBITMQ_USER', 'guest'),
            pass: self::env('RABBITMQ_PASS', 'guest'),
            queue: self::env('RABBITMQ_QUEUE', 'activity_events'),
        );
    }

    private static function env(string $key, string $default): string
    {
        $value = getenv($key);

        return $value === false ? $default : $value;
    }

    private static function envInt(string $key, int $default): int
    {
        $value = getenv($key);

        if ($value === false || $value === '') {
            return $default;
        }

        if (!is_numeric($value)) {
            throw new InvalidArgumentException(sprintf(
                'Environment variable %s must be numeric.',
                $key,
            ));
        }

        return (int) $value;
    }
}
