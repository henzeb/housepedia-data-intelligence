<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface Construction
{
    public function constructionYear(): ?int;

    public function roofType(): ?string;

    public function roofMaterial(): ?string;

    /**
     * @return list<string>|null
     */
    public function insulation(): ?array;

    /**
     * @return list<string>|null
     */
    public function heating(): ?array;

    /**
     * @return list<string>|null
     */
    public function locationTypes(): ?array;

    public function isMonument(): ?bool;

    public function monumentStatus(): ?string;
}
