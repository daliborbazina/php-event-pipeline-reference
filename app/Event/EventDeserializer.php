<?php

declare(strict_types=1);

namespace App\Event;

use InvalidArgumentException;

final class EventDeserializer
{
    /**
     * @throws \JsonException
     */
    public function deserialize(string $json): DomainEvent
    {
        $data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        if (!is_array($data)) {
            throw new InvalidArgumentException('Event payload must decode to an array.');
        }

        // Validation is kept inside the deserializer to keep the example simple and self-contained.
        // For multiple event types or more complex rules, a dedicated validator would be appropriate.
        $type = $this->requireNonEmptyString($data, 'type');

        if ($type !== ActivityRecorded::TYPE) {
            throw new InvalidArgumentException(sprintf('Unsupported event type: %s', $type));
        }

        $payload = $this->requireArray($data, 'payload');

        $id = $this->requireNonEmptyString($data, 'id');
        $occurredAt = $this->requireNonEmptyString($data, 'occurred_at');
        $category = $this->requireNonEmptyString($payload, 'category');
        $source = $this->requireNonEmptyString($payload, 'source');
        $subject = $this->requireNonEmptyString($payload, 'subject');

        return new ActivityRecorded(
            $id,
            $occurredAt,
            [
                'category' => $category,
                'source' => $source,
                'subject' => $subject,
            ],
        );
    }

    /**
     * @param array<string,mixed> $data
     * @param string $key
     * @return string
     */
    private function requireNonEmptyString(array $data, string $key): string
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key]) || $data[$key] === '') {
            throw new InvalidArgumentException(sprintf(
                'Field %s must be a non-empty string.',
                $key,
            ));
        }

        return $data[$key];
    }

    /**
     * @param array<string,mixed> $data
     * @param string $key
     * @return array<string,mixed>
     */
    private function requireArray(array $data, string $key): array
    {
        if (!array_key_exists($key, $data) || !is_array($data[$key])) {
            throw new InvalidArgumentException(sprintf(
                'Field %s must decode to an array.',
                $key,
            ));
        }

        return $data[$key];
    }
}
