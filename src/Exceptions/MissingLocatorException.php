<?php

namespace Henzeb\Housepedia\Data\Intelligence\Exceptions;

class MissingLocatorException extends HousepediaException
{
    public function __construct(string $resource, string $method)
    {
        parent::__construct(sprintf('%s::%s() requires a locator; call one of the with*() methods first.', $resource, $method));
    }
}
