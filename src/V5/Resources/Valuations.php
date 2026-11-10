<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\Resources;

use Henzeb\Housepedia\Data\Intelligence\Concerns\ConfiguresCache;
use Henzeb\Housepedia\Data\Intelligence\Contracts\Input\ScenarioRow;
use Henzeb\Housepedia\Data\Intelligence\Contracts\Valuations as ValuationsContract;
use Henzeb\Housepedia\Data\Intelligence\Transport\Transport;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Results;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\ScenarioValuation;

class Valuations implements ValuationsContract
{
    use ConfiguresCache;

    public function __construct(
        private Transport $transport,
    ) {
    }

    /**
     * @param list<ScenarioRow> $rows
     * @return Results<ScenarioValuation>
     */
    public function scenarios(array $rows): Results
    {
        return Results::of(
            $this->transport->post(
                '/v5/valuations/scenarios',
                ['data' => array_map(static fn (ScenarioRow $row): array => $row->toArray(), $rows)],
            )->payload(),
            ScenarioValuation::from(...),
        );
    }
}
