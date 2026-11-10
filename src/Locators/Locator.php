<?php

namespace Henzeb\Housepedia\Data\Intelligence\Locators;

use Stringable;

class Locator implements Stringable
{
    private function __construct(
        private string $value,
        private string $path,
    ) {
    }

    public static function postcode(string $postcode, int $houseNumber, ?string $extension = null): self
    {
        $parts = [strtoupper(str_replace(' ', '', trim($postcode))), (string)$houseNumber];

        if ($extension === null || $extension === '') {
            return new self(implode('-', $parts), implode('-', $parts));
        }

        return new self(
            implode('-', [...$parts, $extension]),
            implode('-', [...$parts, rawurlencode($extension)]),
        );
    }

    public static function bagId(string $bagId): self
    {
        return new self('bag:' . trim($bagId), 'bag:' . rawurlencode(trim($bagId)));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->path;
    }
}
