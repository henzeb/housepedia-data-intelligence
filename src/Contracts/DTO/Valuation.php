<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface Valuation
{
    public function address(): Address;

    public function valuation(): Estimate;

    public function confidence(): ?Confidence;

    public function meta(): ?Meta;
}
