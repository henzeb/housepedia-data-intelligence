<?php

namespace Henzeb\Housepedia\Data\Intelligence\Exceptions;

class MissingApiKeyException extends HousepediaException
{
    public function __construct(string $method, string $path)
    {
        parent::__construct(sprintf('%s %s requires an API key; none is configured.', $method, $path));
    }
}
