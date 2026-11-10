<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

use DateTimeImmutable;

interface Condition
{
    public function address(): Address;

    public function available(): bool;

    public function source(): ?string;

    public function assessedAt(): ?DateTimeImmutable;

    public function overall(): ?ConditionScore;

    public function modernity(): ?ConditionScore;

    public function finish(): ?ConditionScore;

    public function style(): ?string;

    public function reasoning(): ?string;

    public function meta(): ?Meta;
}
