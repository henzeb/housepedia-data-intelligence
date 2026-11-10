<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface ReferenceTarget
{
    public function objectType(): ?string;

    public function livingAreaInSquareMeters(): ?int;

    public function constructionYear(): ?int;
}
