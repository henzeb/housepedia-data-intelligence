<?php

namespace Henzeb\Housepedia\Data\Intelligence\Exceptions;

use Throwable;

class ApiException extends HousepediaException
{
    /**
     * @param list<array<string, mixed>> $errors
     */
    public function __construct(
        private int $status,
        private string $errorCode,
        private string $title,
        private ?string $detail = null,
        private ?string $requestId = null,
        private array $errors = [],
        private ?string $type = null,
        private ?int $retryAfter = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($detail ?? $title, $status, $previous);
    }

    public function status(): int
    {
        return $this->status;
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function detail(): ?string
    {
        return $this->detail;
    }

    public function requestId(): ?string
    {
        return $this->requestId;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    public function type(): ?string
    {
        return $this->type;
    }

    public function retryAfter(): ?int
    {
        return $this->retryAfter;
    }
}
