<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface Surroundings
{
    public function address(): Address;

    /**
     * @return array<string, NearestPlace|null>
     */
    public function nearest(): array;

    /**
     * @return array<string, int|null>|null
     */
    public function countsInNeighbourhood(): ?array;

    /**
     * @return array<string, mixed>|null
     */
    public function mobility(): ?array;

    public function meta(): ?Meta;
}
