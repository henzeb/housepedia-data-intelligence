<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\Resources;

use DateTimeInterface;
use Henzeb\Housepedia\Data\Intelligence\Concerns\BuildsQuery;
use Henzeb\Housepedia\Data\Intelligence\Concerns\ConfiguresCache;
use Henzeb\Housepedia\Data\Intelligence\Concerns\WithBagId;
use Henzeb\Housepedia\Data\Intelligence\Concerns\WithPostcode;
use Henzeb\Housepedia\Data\Intelligence\Contracts\Enums\PropertyFieldGroup;
use Henzeb\Housepedia\Data\Intelligence\Contracts\Enums\ReferenceSort;
use Henzeb\Housepedia\Data\Intelligence\Contracts\Properties as PropertiesContract;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\MissingLocatorException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\NotFoundException;
use Henzeb\Housepedia\Data\Intelligence\Locators\Locator;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;
use Henzeb\Housepedia\Data\Intelligence\Transport\Transport;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Condition;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\EnergyLabel;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Environment;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Property;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\References;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Surroundings;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Transactions;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Valuation;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\ValuationHistory;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\ValuationPlus;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Woz;

/**
 * @phpstan-consistent-constructor
 */
class Properties implements PropertiesContract
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

    /**
     * @param list<PropertyFieldGroup|string> $fields
     */
    public function find(array $fields = [], bool $includeRd = false): Property
    {
        return Property::from(
            $this->get(__FUNCTION__, '', ['fields' => $this->enumList($fields), 'include' => $this->include($includeRd)]),
        );
    }

    public function energyLabel(): EnergyLabel
    {
        return EnergyLabel::from($this->get(__FUNCTION__, '/energy-label'));
    }

    public function woz(bool $indexed = false): Woz
    {
        return Woz::from($this->get(__FUNCTION__, '/woz', ['index' => $indexed ?: null]));
    }

    public function condition(): Condition
    {
        return Condition::from($this->get(__FUNCTION__, '/condition'));
    }

    public function transactions(): Transactions
    {
        return Transactions::from($this->get(__FUNCTION__, '/transactions'));
    }

    public function hasTransactions(): bool
    {
        $locator = $this->locator ?? throw new MissingLocatorException(static::class, __FUNCTION__);

        try {
            $this->transport->head('/v5/properties/' . $locator . '/transactions');
        } catch (NotFoundException) {
            return false;
        }

        return true;
    }

    public function valuation(?DateTimeInterface $asOfDate = null): Valuation
    {
        return Valuation::from($this->get(__FUNCTION__, '/valuation', ['as_of_date' => $this->date($asOfDate)]));
    }

    public function valuationPlus(?DateTimeInterface $asOfDate = null): ValuationPlus
    {
        return ValuationPlus::from(
            $this->get(__FUNCTION__, '/valuation/plus', ['as_of_date' => $this->date($asOfDate)]),
        );
    }

    public function valuationHistory(
        ?DateTimeInterface $from = null,
        ?DateTimeInterface $to = null,
        bool $includeRd = false,
    ): ValuationHistory {
        return ValuationHistory::from(
            $this->get(__FUNCTION__, '/valuation/history', [
                'from' => $this->date($from),
                'to' => $this->date($to),
                'include' => $this->include($includeRd),
            ]),
        );
    }

    public function references(
        ?int $limit = null,
        ?int $maxDistanceInMeters = null,
        ?int $maxAgeMonths = null,
        ?bool $sameType = null,
        ?int $minLivingAreaInSquareMeters = null,
        ?int $maxLivingAreaInSquareMeters = null,
        ?int $minConstructionYear = null,
        ?int $maxConstructionYear = null,
        ?DateTimeInterface $asOfDate = null,
        ReferenceSort|string|null $sort = null,
    ): References {
        return References::from(
            $this->get(__FUNCTION__, '/references', [
                'limit' => $limit,
                'max_distance_m' => $maxDistanceInMeters,
                'max_age_months' => $maxAgeMonths,
                'same_type' => $sameType,
                'min_living_area_m2' => $minLivingAreaInSquareMeters,
                'max_living_area_m2' => $maxLivingAreaInSquareMeters,
                'min_construction_year' => $minConstructionYear,
                'max_construction_year' => $maxConstructionYear,
                'as_of_date' => $this->date($asOfDate),
                'sort' => $this->enumValue($sort),
            ]),
        );
    }

    public function surroundings(): Surroundings
    {
        return Surroundings::from($this->get(__FUNCTION__, '/surroundings'));
    }

    public function environment(): Environment
    {
        return Environment::from($this->get(__FUNCTION__, '/environment'));
    }

    /**
     * @param array<string, string|int|float|bool|null> $query
     */
    private function get(string $method, string $suffix, array $query = []): Payload
    {
        $locator = $this->locator ?? throw new MissingLocatorException(static::class, $method);

        return $this->transport->get('/v5/properties/' . $locator . $suffix, $query)->payload();
    }
}
