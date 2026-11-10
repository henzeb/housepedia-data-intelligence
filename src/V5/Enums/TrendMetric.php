<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\Enums;

use Henzeb\Housepedia\Data\Intelligence\Contracts\Enums\TrendMetric as TrendMetricContract;

enum TrendMetric: string implements TrendMetricContract
{
    case TransactionPricePerSquareMeter = 'transaction_price_per_m2';
    case AskingPricePerSquareMeter = 'asking_price_per_m2';
}
