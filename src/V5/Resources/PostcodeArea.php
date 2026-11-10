<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\Resources;

use Henzeb\Housepedia\Data\Intelligence\Concerns\BuildsQuery;
use Henzeb\Housepedia\Data\Intelligence\Concerns\ConfiguresCache;
use Henzeb\Housepedia\Data\Intelligence\Contracts\PostcodeArea as PostcodeAreaContract;
use Henzeb\Housepedia\Data\Intelligence\Locators\PostcodeAreaLocator;
use Henzeb\Housepedia\Data\Intelligence\Transport\Transport;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Area;

class PostcodeArea implements PostcodeAreaContract
{
    use BuildsQuery;
    use ConfiguresCache;

    public function __construct(
        private Transport $transport,
        private PostcodeAreaLocator $area,
    ) {
    }

    public function find(bool $includeRd = false): Area
    {
        return Area::from(
            $this->transport->get('/v5/areas/' . $this->area, ['include' => $this->include($includeRd)])->payload(),
        );
    }
}
