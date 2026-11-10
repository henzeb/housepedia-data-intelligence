<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\Resources;

use Henzeb\Housepedia\Data\Intelligence\Concerns\BuildsQuery;
use Henzeb\Housepedia\Data\Intelligence\Concerns\ConfiguresCache;
use Henzeb\Housepedia\Data\Intelligence\Concerns\WithBagId;
use Henzeb\Housepedia\Data\Intelligence\Concerns\WithPostcode;
use Henzeb\Housepedia\Data\Intelligence\Contracts\Addresses as AddressesContract;
use Henzeb\Housepedia\Data\Intelligence\Contracts\Enums\SuggestionType;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\MissingLocatorException;
use Henzeb\Housepedia\Data\Intelligence\Locators\Locator;
use Henzeb\Housepedia\Data\Intelligence\Transport\Transport;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Address;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\AddressSuggestion;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Meta;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Results;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\ReverseResults;

/**
 * @phpstan-consistent-constructor
 */
class Addresses implements AddressesContract
{
    use BuildsQuery;
    use ConfiguresCache;
    use WithBagId;
    use WithPostcode;

    public function __construct(
        private Transport $transport,
        private ?Locator $locator = null,
    ) {
    }

    public function withLocator(Locator $locator): static
    {
        return new static($this->transport, $locator);
    }

    public function find(bool $includeRd = false): Address
    {
        return Address::from(
            $this->transport->get($this->path(__FUNCTION__), ['include' => $this->include($includeRd)])->payload(),
        );
    }

    /**
     * @return Results<Address>
     */
    public function findAll(bool $includeRd = false): Results
    {
        $payload = $this->transport->get(
            $this->path(__FUNCTION__),
            ['all' => true, 'include' => $this->include($includeRd)],
        )->payload();

        if ($payload->array('data') !== null) {
            return Results::of($payload, Address::from(...));
        }

        return new Results([Address::from($payload)], $payload->nested('meta', Meta::from(...)));
    }

    /**
     * @param list<SuggestionType|string> $types
     * @return Results<AddressSuggestion>
     */
    public function suggest(string $query, ?int $limit = null, array $types = []): Results
    {
        return Results::of(
            $this->transport->get(
                '/v5/addresses/suggest',
                ['q' => $query, 'limit' => $limit, 'types' => $this->enumList($types)],
            )->payload(),
            AddressSuggestion::from(...),
        );
    }

    public function reverse(
        float|string $latitude,
        float|string $longitude,
        ?int $radiusInMeters = null,
        ?int $limit = null,
    ): ReverseResults {
        return ReverseResults::from(
            $this->transport->get('/v5/addresses/reverse', [
                'lat' => $this->coordinate('latitude', $latitude),
                'lon' => $this->coordinate('longitude', $longitude),
                'radius_m' => $radiusInMeters,
                'limit' => $limit,
            ])->payload(),
        );
    }

    private function path(string $method): string
    {
        $locator = $this->locator ?? throw new MissingLocatorException(static::class, $method);

        return '/v5/addresses/' . $locator;
    }
}
