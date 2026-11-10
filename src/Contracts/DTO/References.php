<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface References
{
    public function target(): ReferenceTarget;

    /**
     * @return list<Reference>
     */
    public function references(): array;

    public function meta(): ?Meta;
}
