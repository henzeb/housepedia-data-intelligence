<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface Property
{
    public function address(): Address;

    public function type(): ?PropertyType;

    public function dimensions(): ?Dimensions;

    public function rooms(): ?Rooms;

    public function outdoor(): ?Outdoor;

    public function construction(): ?Construction;

    public function energy(): ?Energy;

    public function ownership(): ?Ownership;

    public function maintenance(): ?Maintenance;

    public function meta(): ?Meta;
}
