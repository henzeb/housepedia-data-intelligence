<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface Rooms
{
    public function rooms(): ?int;

    public function bedrooms(): ?int;

    public function bathrooms(): ?int;

    public function separateToilets(): ?int;

    public function floors(): ?int;

    public function floorLevel(): ?int;
}
