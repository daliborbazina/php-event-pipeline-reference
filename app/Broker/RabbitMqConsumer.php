<?php

declare(strict_types=1);

namespace App\Broker;

use Exception;
use InvalidArgumentException;
use PhpAmqpLib\Message\AMQPMessage;

final readonly class RabbitMqConsumer
{
    public function __construct(
        private RabbitMqConnectionFactory $connectionFactory,
        private string $queue,
    ) {
        if ($this->queue === '') {
            throw new InvalidArgumentException('Queue name must not be empty.');
        }
    }

    /**
     * @param callable(string): void $handler
     * @throws Exception
     */
    public function consume(callable $handler): void
    {
        $connection = $this->connectionFactory->create();
        $channel = null;

        try {
            $channel = $connection->channel();

            // Queue declaration is handled inside the publisher to keep the example self-contained.
            // In a production system, infrastructure setup would be separated from message publishing.
            $channel->queue_declare($this->queue, false, true, false, false);

            $callback = function (AMQPMessage $message) use ($handler): void {
                $handler($message->getBody());
                // Acknowledge only after successful handler execution.
                $message->ack();
            };

            // Process one message at a time to keep worker behavior predictable.
            $channel->basic_qos(0, 1, null);
            $channel->basic_consume($this->queue, '', false, false, false, false, $callback);

            while ($channel->is_consuming()) {
                $channel->wait();
            }

        } finally {
            $channel?->close();
            $connection->close();
        }
    }
}
