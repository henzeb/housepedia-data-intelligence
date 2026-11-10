<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts\DTO;

interface Environment
{
    public function address(): Address;

    public function foundation(): ?Foundation;

    public function subsidence(): ?Subsidence;

    public function noise(): ?Noise;

    public function air(): ?AirQuality;

    public function heat(): ?Heat;

    public function lightPollution(): ?float;

    public function environmentalHealthRisk(): ?float;

    public function beeRichness(): ?float;

    /**
     * @return array<string, string>
     */
    public function sources(): array;

    public function meta(): ?Meta;
}
