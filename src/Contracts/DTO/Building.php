<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface Building
{
    public function bagPandId(): string;

    public function constructionYear(): ?int;

    public function bagStatus(): ?string;

    public function unitsCount(): int;

    public function residentialUnitsCount(): int;

    /**
     * @return list<BuildingUnit>
     */
    public function units(): array;

    public function location(): ?Location;

    public function street(): ?string;

    public function postcode(): ?string;

    public function city(): ?string;

    public function meta(): ?Meta;
}
