<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface Area
{
    public function type(): string;

    public function code(): string;

    public function housepediaId(): ?string;

    public function name(): ?string;

    public function slug(): ?string;

    /**
     * @return list<AreaParent>
     */
    public function parents(): array;

    public function centroid(): ?Location;

    public function cbsVintageYear(): ?int;

    /**
     * @return array<string, mixed>|null
     */
    public function geometry(): ?array;

    public function meta(): ?Meta;
}
