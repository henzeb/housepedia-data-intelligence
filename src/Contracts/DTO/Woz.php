<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface Woz
{
    public function address(): Address;

    public function latest(): ?WozValue;

    /**
     * @return list<WozValue>
     */
    public function values(): array;

    public function meta(): ?Meta;
}
