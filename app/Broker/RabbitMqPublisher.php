<?php

declare(strict_types=1);

namespace App\Broker;

use Exception;
use InvalidArgumentException;
use PhpAmqpLib\Message\AMQPMessage;

final readonly class RabbitMqPublisher
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
     * @throws Exception
     */
    public function publish(string $payload): void
    {
        $connection = $this->connectionFactory->create();
        $channel = null;

        try {
            $channel = $connection->channel();

            // Queue declaration is handled inside the publisher to keep the example self-contained.
            // In a production system, infrastructure setup would be separated from message publishing.
            $channel->queue_declare($this->queue, false, true, false, false);

            $message = new AMQPMessage($payload, [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            ]);

            $channel->basic_publish($message, '', $this->queue);
        } finally {
            $channel?->close();
            $connection->close();
        }
    }
}
