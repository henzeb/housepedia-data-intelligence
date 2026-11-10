<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface Account
{
    public function accountId(): string;

    public function type(): string;

    public function name(): ?string;

    public function locale(): string;

    public function plan(): string;

    public function balance(): Balance;

    public function spendCap(): SpendCap;

    public function limits(): AccountLimits;

    /**
     * @return list<string>
     */
    public function entitlements(): array;

    public function termsVersion(): ?string;

    public function meta(): ?Meta;
}
