<?php

namespace Henzeb\Housepedia\Data\Intelligence\Exceptions;

class InvalidCoordinateException extends HousepediaException
{
    public function __construct(string $name, float|string $value)
    {
        parent::__construct(sprintf('The %s "%s" is not a decimal number such as 52.34536.', $name, $this->text($value)));
    }

    private function text(float|string $value): string
    {
        return match (true) {
            is_string($value) => $value,
            is_nan($value) => 'NAN',
            is_infinite($value) => $value > 0 ? 'INF' : '-INF',
            default => sprintf('%.17G', $value),
        };
    }
}
