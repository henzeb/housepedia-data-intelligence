<?php

namespace Henzeb\Housepedia\Data\Intelligence;

use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Meta;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Response
{
    private Payload $payload;

    private ?RateLimit $rateLimit;

    /**
     * @param array<string, list<string>> $headers
     * @param array<array-key, mixed> $data
     */
    public function __construct(
        private int $status,
        private array $headers,
        array $data,
    ) {
        $this->rateLimit = RateLimit::fromHeaders($headers);
        $this->payload = new Payload($data, '', $this->rateLimit);
    }

    public function rateLimit(): ?RateLimit
    {
        return $this->rateLimit;
    }

    public function status(): int
    {
        return $this->status;
    }

    /**
     * @return array<string, list<string>>
     */
    public function headers(): array
    {
        return $this->headers;
    }

    public function header(string $name): ?string
    {
        foreach ($this->headers as $header => $values) {
            if (strcasecmp($header, $name) === 0) {
                return $values[0] ?? null;
            }
        }

        return null;
    }

    public function meta(): ?Meta
    {
        return $this->payload->nested('meta', Meta::from(...));
    }

    /**
     * @return array<array-key, mixed>
     */
    public function raw(): array
    {
        return $this->payload->all();
    }

    public function payload(): Payload
    {
        return $this->payload;
    }

    public function has(string $key): bool
    {
        return $this->payload->has($key);
    }

    public function string(string $key): ?string
    {
        return $this->payload->string($key);
    }

    public function integer(string $key): ?int
    {
        return $this->payload->integer($key);
    }

    public function float(string $key): ?float
    {
        return $this->payload->float($key);
    }

    public function boolean(string $key): ?bool
    {
        return $this->payload->boolean($key);
    }

    /**
     * @return array<array-key, mixed>|null
     */
    public function array(string $key): ?array
    {
        return $this->payload->array($key);
    }
}
