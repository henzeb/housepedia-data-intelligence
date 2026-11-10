<?php

namespace Henzeb\Housepedia\Data\Intelligence\Support;

use DateTimeImmutable;
use Exception;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\UnexpectedResponseTypeException;
use Henzeb\Housepedia\Data\Intelligence\RateLimit;

class Payload
{
    /**
     * @param array<array-key, mixed> $data
     */
    public function __construct(
        private array $data,
        private string $path = '',
        private ?RateLimit $rateLimit = null,
    ) {
    }

    public function rateLimit(): ?RateLimit
    {
        return $this->rateLimit;
    }

    /**
     * @return array<array-key, mixed>
     */
    public function all(): array
    {
        return $this->data;
    }

    public function has(string $key): bool
    {
        return $this->value($key) !== null;
    }

    public function value(string $key): mixed
    {
        $value = $this->data;

        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return null;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    public function string(string $key): ?string
    {
        $value = $this->value($key);

        if ($value === null || is_string($value)) {
            return $value;
        }

        throw UnexpectedResponseTypeException::type($this->path($key), 'string', $value);
    }

    public function requireString(string $key): string
    {
        return $this->string($key) ?? throw UnexpectedResponseTypeException::missing($this->path($key));
    }

    public function integer(string $key): ?int
    {
        $value = $this->value($key);

        if ($value === null || is_int($value)) {
            return $value;
        }

        if (is_float($value) && floor($value) === $value) {
            return (int)$value;
        }

        throw UnexpectedResponseTypeException::type($this->path($key), 'int', $value);
    }

    public function requireInteger(string $key): int
    {
        return $this->integer($key) ?? throw UnexpectedResponseTypeException::missing($this->path($key));
    }

    public function float(string $key): ?float
    {
        $value = $this->value($key);

        if ($value === null) {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return (float)$value;
        }

        throw UnexpectedResponseTypeException::type($this->path($key), 'float', $value);
    }

    public function requireFloat(string $key): float
    {
        return $this->float($key) ?? throw UnexpectedResponseTypeException::missing($this->path($key));
    }

    public function boolean(string $key): ?bool
    {
        $value = $this->value($key);

        if ($value === null || is_bool($value)) {
            return $value;
        }

        throw UnexpectedResponseTypeException::type($this->path($key), 'bool', $value);
    }

    public function requireBoolean(string $key): bool
    {
        return $this->boolean($key) ?? throw UnexpectedResponseTypeException::missing($this->path($key));
    }

    public function integerOrString(string $key): int|string|null
    {
        $value = $this->value($key);

        if ($value === null || is_int($value) || is_string($value)) {
            return $value;
        }

        throw UnexpectedResponseTypeException::type($this->path($key), 'int|string', $value);
    }

    public function date(string $key): ?DateTimeImmutable
    {
        $value = $this->string($key);

        if ($value === null) {
            return null;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (Exception) {
            throw UnexpectedResponseTypeException::type($this->path($key), 'date', $value);
        }
    }

    public function requireDate(string $key): DateTimeImmutable
    {
        return $this->date($key) ?? throw UnexpectedResponseTypeException::missing($this->path($key));
    }

    /**
     * @return array<array-key, mixed>|null
     */
    public function array(string $key): ?array
    {
        $value = $this->value($key);

        if ($value === null || is_array($value)) {
            return $value;
        }

        throw UnexpectedResponseTypeException::type($this->path($key), 'array', $value);
    }

    public function object(string $key): ?self
    {
        $value = $this->array($key);

        if ($value === null) {
            return null;
        }

        return $this->child($value, $key);
    }

    /**
     * @template T
     * @param callable(Payload): T $factory
     * @return T|null
     */
    public function nested(string $key, callable $factory): mixed
    {
        $payload = $this->object($key);

        if ($payload === null) {
            return null;
        }

        return $factory($payload);
    }

    /**
     * @template T
     * @param callable(Payload): T $factory
     * @return T
     */
    public function requireNested(string $key, callable $factory): mixed
    {
        $payload = $this->object($key) ?? throw UnexpectedResponseTypeException::missing($this->path($key));

        return $factory($payload);
    }

    /**
     * @template T
     * @param callable(Payload): T $factory
     * @return list<T>
     */
    public function list(string $key, callable $factory): array
    {
        return $this->nullableList($key, $factory) ?? [];
    }

    /**
     * @template T
     * @param callable(Payload): T $factory
     * @return list<T>|null
     */
    public function nullableList(string $key, callable $factory): ?array
    {
        $items = $this->array($key);

        if ($items === null) {
            return null;
        }

        $list = [];

        foreach (array_values($items) as $index => $item) {
            if (!is_array($item)) {
                throw UnexpectedResponseTypeException::type($this->path($key . '.' . $index), 'array', $item);
            }

            $list[] = $factory($this->child($item, $key . '.' . $index));
        }

        return $list;
    }

    /**
     * @return list<string>
     */
    public function strings(string $key): array
    {
        return $this->nullableStrings($key) ?? [];
    }

    /**
     * @return list<string>|null
     */
    public function nullableStrings(string $key): ?array
    {
        $items = $this->array($key);

        if ($items === null) {
            return null;
        }

        $list = [];

        foreach (array_values($items) as $index => $item) {
            if (!is_string($item)) {
                throw UnexpectedResponseTypeException::type($this->path($key . '.' . $index), 'string', $item);
            }

            $list[] = $item;
        }

        return $list;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function arrays(string $key): array
    {
        $items = $this->array($key) ?? [];

        $list = [];

        foreach (array_values($items) as $index => $item) {
            if (!is_array($item)) {
                throw UnexpectedResponseTypeException::type($this->path($key . '.' . $index), 'array', $item);
            }

            $list[] = $this->stringKeys($item);
        }

        return $list;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function associative(string $key): ?array
    {
        $value = $this->array($key);

        if ($value === null) {
            return null;
        }

        return $this->stringKeys($value);
    }

    /**
     * @template T
     * @param callable(Payload): T $factory
     * @return array<string, T|null>
     */
    public function objectMap(string $key, callable $factory): array
    {
        $map = [];

        foreach ($this->associative($key) ?? [] as $name => $item) {
            if ($item !== null && !is_array($item)) {
                throw UnexpectedResponseTypeException::type($this->path($key . '.' . $name), 'array', $item);
            }

            $map[$name] = $item === null ? null : $factory($this->child($item, $key . '.' . $name));
        }

        return $map;
    }

    /**
     * @return array<string, int|null>|null
     */
    public function integerMap(string $key): ?array
    {
        $values = $this->associative($key);

        if ($values === null) {
            return null;
        }

        $map = [];

        foreach (array_keys($values) as $name) {
            $map[$name] = $this->integer($key . '.' . $name);
        }

        return $map;
    }

    /**
     * @return array<string, string>
     */
    public function stringMap(string $key): array
    {
        $map = [];

        foreach (array_keys($this->associative($key) ?? []) as $name) {
            $map[$name] = $this->requireString($key . '.' . $name);
        }

        return $map;
    }

    /**
     * @param array<array-key, mixed> $value
     * @return array<string, mixed>
     */
    private function stringKeys(array $value): array
    {
        $result = [];

        foreach ($value as $name => $item) {
            $result[(string)$name] = $item;
        }

        return $result;
    }

    /**
     * @param array<array-key, mixed> $data
     */
    private function child(array $data, string $key): self
    {
        return new self($data, $this->path($key), $this->rateLimit);
    }

    private function path(string $key): string
    {
        return $this->path === '' ? $key : $this->path . '.' . $key;
    }
}
