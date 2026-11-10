<?php

namespace Henzeb\Housepedia\Data\Intelligence\Contracts;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Address;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\AddressSuggestion;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Results;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\ReverseAddress;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\ReverseResults;
use Henzeb\Housepedia\Data\Intelligence\Contracts\Enums\SuggestionType;
use Henzeb\Housepedia\Data\Intelligence\Locators\Locator;

interface Addresses
{
    public function withLocator(Locator $locator): static;

    public function find(bool $includeRd = false): Address;

    /**
     * @return Results<Address>
     */
    public function findAll(bool $includeRd = false): Results;

    /**
     * @param list<SuggestionType|string> $types
     * @return Results<AddressSuggestion>
     */
    public function suggest(string $query, ?int $limit = null, array $types = []): Results;

    /**
     * @return ReverseResults<ReverseAddress>
     */
    public function reverse(
        string|float $latitude,
        string|float $longitude,
        ?int $radiusInMeters = null,
        ?int $limit = null,
    ): ReverseResults;

    public function withMaxCacheAge(int $seconds): static;

    public function withoutCache(): static;

    public function withBagId(string $bagId): static;

    public function withPostcode(string $postcode, int $houseNumber, ?string $extension = null): static;
}
