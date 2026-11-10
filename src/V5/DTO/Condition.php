<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use DateTimeImmutable;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Condition as ConditionContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Condition implements ConditionContract
{
    public function __construct(
        private Address $address,
        private bool $available,
        private ?string $source,
        private ?DateTimeImmutable $assessedAt,
        private ?ConditionScore $overall,
        private ?ConditionScore $modernity,
        private ?ConditionScore $finish,
        private ?string $style,
        private ?string $reasoning,
        private ?Meta $meta,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireNested('address', Address::from(...)),
            $payload->requireBoolean('available'),
            $payload->string('source'),
            $payload->date('assessed_at'),
            $payload->nested('overall', ConditionScore::from(...)),
            $payload->nested('modernity', ConditionScore::from(...)),
            $payload->nested('finish', ConditionScore::from(...)),
            $payload->string('style'),
            $payload->string('reasoning'),
            $payload->nested('meta', Meta::from(...)),
        );
    }

    public function address(): Address
    {
        return $this->address;
    }

    public function available(): bool
    {
        return $this->available;
    }

    public function source(): ?string
    {
        return $this->source;
    }

    public function assessedAt(): ?DateTimeImmutable
    {
        return $this->assessedAt;
    }

    public function overall(): ?ConditionScore
    {
        return $this->overall;
    }

    public function modernity(): ?ConditionScore
    {
        return $this->modernity;
    }

    public function finish(): ?ConditionScore
    {
        return $this->finish;
    }

    public function style(): ?string
    {
        return $this->style;
    }

    public function reasoning(): ?string
    {
        return $this->reasoning;
    }

    public function meta(): ?Meta
    {
        return $this->meta;
    }
}
