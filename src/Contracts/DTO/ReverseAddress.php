<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface ReverseAddress
{
    public function bagId(): ?string;

    public function postcode(): ?string;

    public function houseNumber(): ?int;

    public function houseNumberExt(): ?string;

    public function street(): ?string;

    public function city(): ?string;

    public function municipality(): ?string;

    public function province(): ?string;

    public function location(): ?Location;

    public function rd(): ?RdLocation;

    public function housepediaUrl(): ?string;

    public function distanceInMeters(): float;
}
