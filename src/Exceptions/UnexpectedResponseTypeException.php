<?php

namespace Henzeb\Housepedia\Data\Intelligence\Exceptions;

class UnexpectedResponseTypeException extends HousepediaException
{
    public static function type(string $path, string $expected, mixed $actual): self
    {
        return new self(sprintf('Expected [%s] to be of type %s, got %s.', $path, $expected, get_debug_type($actual)));
    }

    public static function missing(string $path): self
    {
        return new self(sprintf('Expected [%s] to be present in the response.', $path));
    }

    public static function body(int $status): self
    {
        return new self(sprintf('Expected a JSON object as response body for status %d.', $status));
    }
}
