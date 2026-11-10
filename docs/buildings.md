# Buildings

Every method and every field of the response objects: [buildings reference](reference/buildings.md).

`GET /v5/buildings/{bag_pand_id}` returns a building and its units. The BAG pand id is
available on an address as `bagPandId()`.

```php
$building = $client->buildings()
    ->withBagPandId('0363100012345678')
    ->find();

$building->constructionYear();
$building->unitsCount();

foreach ($building->units() as $unit) {
    $unit->houseNumber();
    $unit->livingAreaInSquareMeters();
}
```
