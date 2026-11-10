<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface AddressSuggestion
{
    public function type(): string;

    public function label(): string;

    public function bagId(): ?string;

    public function postcode(): ?string;

    public function houseNumber(): ?int;

    public function houseNumberExt(): ?string;

    public function street(): ?string;

    public function city(): ?string;

    public function municipality(): ?string;

    public function location(): ?Location;

    public function addressCount(): ?int;
}
