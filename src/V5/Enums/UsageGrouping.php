<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\Enums;

use Henzeb\Housepedia\Data\Intelligence\Contracts\Enums\UsageGrouping as UsageGroupingContract;

enum UsageGrouping: string implements UsageGroupingContract
{
    case Day = 'day';
    case Endpoint = 'endpoint';
    case Key = 'key';
}
