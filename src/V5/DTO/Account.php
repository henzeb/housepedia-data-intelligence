<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Account as AccountContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Account implements AccountContract
{
    /**
     * @param list<string> $entitlements
     */
    public function __construct(
        private string $accountId,
        private string $type,
        private ?string $name,
        private string $locale,
        private string $plan,
        private Balance $balance,
        private SpendCap $spendCap,
        private AccountLimits $limits,
        private array $entitlements,
        private ?string $termsVersion,
        private ?Meta $meta,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireString('account_id'),
            $payload->requireString('type'),
            $payload->string('name'),
            $payload->requireString('locale'),
            $payload->requireString('plan'),
            $payload->requireNested('balance', Balance::from(...)),
            $payload->requireNested('spend_cap', SpendCap::from(...)),
            $payload->requireNested('limits', AccountLimits::from(...)),
            $payload->strings('entitlements'),
            $payload->string('terms_version'),
            $payload->nested('meta', Meta::from(...)),
        );
    }

    public function accountId(): string
    {
        return $this->accountId;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function locale(): string
    {
        return $this->locale;
    }

    public function plan(): string
    {
        return $this->plan;
    }

    public function balance(): Balance
    {
        return $this->balance;
    }

    public function spendCap(): SpendCap
    {
        return $this->spendCap;
    }

    public function limits(): AccountLimits
    {
        return $this->limits;
    }

    /**
     * @return list<string>
     */
    public function entitlements(): array
    {
        return $this->entitlements;
    }

    public function termsVersion(): ?string
    {
        return $this->termsVersion;
    }

    public function meta(): ?Meta
    {
        return $this->meta;
    }
}
