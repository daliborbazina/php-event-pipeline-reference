<?php

declare(strict_types=1);

namespace App\Broker;

use Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPIOException;
use RuntimeException;

final readonly class RabbitMqConnectionFactory
{
    public const int ATTEMPTS = 10;
    public const int DELAY = 500000;

    public function __construct(
        private RabbitMqConfig $config,
    ) {
    }

    /**
     * @throws Exception
     */
    public function create(): AMQPStreamConnection
    {
        $lastException = null;

        for ($attempt = 1; $attempt <= self::ATTEMPTS; $attempt++) {
            try {
                return new AMQPStreamConnection(
                    $this->config->host,
                    $this->config->port,
                    $this->config->user,
                    $this->config->pass,
                );
            } catch (AMQPIOException $exception) {
                $lastException = $exception;

                if ($attempt === self::ATTEMPTS) {
                    break;
                }

                usleep(self::DELAY);
            }
        }

        throw new RuntimeException(
            sprintf('Unable to connect to RabbitMQ after %d attempts.', self::ATTEMPTS),
            previous: $lastException,
        );
    }
}
