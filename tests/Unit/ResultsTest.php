<?php

use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Location;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Meta;
use Henzeb\Housepedia\Data\Intelligence\V5\DTO\Results;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

it('holds the list and the meta of a list answer', function () {
    $results = Results::of(
        new Payload(['data' => [['lat' => 1, 'lon' => 2], ['lat' => 3, 'lon' => 4]], 'meta' => ['request_id' => 'req_1']]),
        Location::from(...),
    );

    expect($results->data())->toHaveCount(2)->each->toBeInstanceOf(Location::class)
        ->and($results->meta())->toBeInstanceOf(Meta::class)
        ->and($results->meta()?->requestId())->toBe('req_1')
        ->and($results)->toHaveCount(2);
});

it('iterates the list with keys from zero', function () {
    $results = new Results(['a', 'b'], null);
    $iterated = [];

    foreach ($results as $key => $item) {
        $iterated[$key] = $item;
    }

    expect($iterated)->toBe([0 => 'a', 1 => 'b']);
});

it('is empty without data', function () {
    $results = Results::of(new Payload([]), Location::from(...));

    expect($results->data())->toBe([])
        ->and($results->meta())->toBeNull()
        ->and(count($results))->toBe(0);
});
