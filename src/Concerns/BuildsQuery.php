<?php

namespace Henzeb\Housepedia\Data\Intelligence\Concerns;

use BackedEnum;
use DateTimeInterface;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\InvalidCoordinateException;

trait BuildsQuery
{
    private function date(?DateTimeInterface $date): ?string
    {
        return $date?->format('Y-m-d');
    }

    private function dateTime(?DateTimeInterface $date): ?string
    {
        return $date?->format(DateTimeInterface::RFC3339);
    }

    /**
     * @param list<BackedEnum|string> $values
     */
    private function enumList(array $values): ?string
    {
        if ($values === []) {
            return null;
        }

        return implode(',', array_map($this->enumValue(...), $values));
    }

    private function enumValue(BackedEnum|string|null $value): ?string
    {
        return $value instanceof BackedEnum ? (string)$value->value : $value;
    }

    private function coordinate(string $name, float|string $value): float|string
    {
        $valid = is_string($value) ? preg_match('/^-?\d+(\.\d+)?$/', $value) === 1 : is_finite($value);

        if (!$valid) {
            throw new InvalidCoordinateException($name, $value);
        }

        return $value;
    }

    private function include(bool $rd): ?string
    {
        return $rd ? 'rd' : null;
    }
}
