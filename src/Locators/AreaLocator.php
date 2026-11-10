<?php

namespace Henzeb\Housepedia\Data\Intelligence\Locators;

use Stringable;

class AreaLocator implements Stringable
{
    private function __construct(
        private string $value,
    ) {
    }

    public static function neighbourhood(string $code): self
    {
        return self::level('neighbourhood', $code);
    }

    public static function district(string $code): self
    {
        return self::level('district', $code);
    }

    public static function municipality(string $code): self
    {
        return self::level('municipality', $code);
    }

    public static function province(string $code): self
    {
        return self::level('province', $code);
    }

    public static function national(): self
    {
        return new self('national');
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private static function level(string $level, string $code): self
    {
        return new self($level . ':' . rawurlencode(trim($code)));
    }
}
