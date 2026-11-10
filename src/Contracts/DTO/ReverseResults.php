<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

/**
 * @template-covariant T of ReverseAddress
 * @extends Results<T>
 */
interface ReverseResults extends Results
{
    public function areas(): ?AreaHierarchy;
}
