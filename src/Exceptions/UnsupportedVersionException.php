<?php

namespace Henzeb\Housepedia\Data\Intelligence\Exceptions;

class UnsupportedVersionException extends HousepediaException
{
    /**
     * @param list<string> $supported
     */
    public function __construct(int|string $version, array $supported)
    {
        parent::__construct(sprintf('API version "%s" is not supported; use one of %s.', $version, implode(', ', $supported)));
    }
}
