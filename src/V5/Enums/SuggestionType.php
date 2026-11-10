<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\Enums;

use Henzeb\Housepedia\Data\Intelligence\Contracts\Enums\SuggestionType as SuggestionTypeContract;

enum SuggestionType: string implements SuggestionTypeContract
{
    case Address = 'address';
    case Street = 'street';
    case Postcode = 'postcode';
}
