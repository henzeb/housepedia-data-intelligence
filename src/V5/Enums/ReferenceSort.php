<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\Enums;

use Henzeb\Housepedia\Data\Intelligence\Contracts\Enums\ReferenceSort as ReferenceSortContract;

enum ReferenceSort: string implements ReferenceSortContract
{
    case Similarity = 'similarity';
    case Distance = 'distance';
    case Date = 'date';
}
