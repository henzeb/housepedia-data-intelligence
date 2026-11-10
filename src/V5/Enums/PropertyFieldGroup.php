<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\Enums;

use Henzeb\Housepedia\Data\Intelligence\Contracts\Enums\PropertyFieldGroup as PropertyFieldGroupContract;

enum PropertyFieldGroup: string implements PropertyFieldGroupContract
{
    case Type = 'type';
    case Dimensions = 'dimensions';
    case Rooms = 'rooms';
    case Outdoor = 'outdoor';
    case Construction = 'construction';
    case Energy = 'energy';
    case Ownership = 'ownership';
    case Maintenance = 'maintenance';
}
