<?php

namespace Henzeb\Housepedia\Data\Intelligence;

use DateTimeImmutable;

class RateLimit
{
    public function __construct(
        private int $limit,
        private int $remaining,
        private int $resetInSeconds,
        private DateTimeImmutable $receivedAt = new DateTimeImmutable(),
    ) {
    }

    /**
     * @param array<string, list<string>> $headers
     */
    public static function fromHeaders(array $headers, DateTimeImmutable $receivedAt = new DateTimeImmutable()): ?self
    {
        $values = [];

        foreach (['limit', 'remaining', 'reset'] as $name) {
            $value = self::header($headers, 'X-RateLimit-' . $name);

            if ($value === null || !ctype_digit($value)) {
                return null;
            }

            $values[] = (int)$value;
        }

        return new self($values[0], $values[1], $values[2], $receivedAt);
    }

    public function limit(): int
    {
        return $this->limit;
    }

    public function remaining(): int
    {
        return $this->remaining;
    }

    public function resetInSeconds(): int
    {
        return $this->resetInSeconds;
    }

    public function resetsAt(): DateTimeImmutable
    {
        return $this->receivedAt->modify('+' . $this->resetInSeconds . ' seconds');
    }

    /**
     * @param array<string, list<string>> $headers
     */
    private static function header(array $headers, string $name): ?string
    {
        foreach ($headers as $header => $values) {
            if (strcasecmp($header, $name) === 0) {
                return isset($values[0]) ? trim($values[0]) : null;
            }
        }

        return null;
    }
}
