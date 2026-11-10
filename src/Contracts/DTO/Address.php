<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface Address
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

    public function bagVerblijfsobjectId(): ?string;

    public function bagPandId(): ?string;

    public function bagStatus(): ?string;

    public function isResidential(): ?bool;

    public function neighbourhood(): ?AreaReference;

    public function district(): ?AreaReference;

    public function municipalityCode(): ?string;

    public function provinceCode(): ?string;

    public function meta(): ?Meta;
}
