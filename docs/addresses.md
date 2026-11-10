# Addresses

Every method and every field of the response objects: [addresses reference](reference/addresses.md).

- [Finding an address](#finding-an-address)
- [Every home at a house number](#every-home-at-a-house-number)
- [Autocomplete](#autocomplete)
- [Reverse geocoding](#reverse-geocoding)

<a name="finding-an-address"></a>
## Finding an address

`GET /v5/addresses/{locator}` checks an address and returns it in its standard form, with
its BAG ids and the areas it lies in.

```php
$address = $client->addresses()
    ->withPostcode('1076LJ', 12, '2')
    ->find();

$address->street();                 // Amazonenstraat
$address->city();                   // Amsterdam
$address->bagId();                  // 0363200000028422
$address->bagPandId();
$address->neighbourhood()?->code(); // BU0363KH05
$address->location()?->lat();
```

Pass `true` to also receive the Dutch RD coordinates in `rd()`:

```php
$client->addresses()->withBagId('0363200000028422')->find(includeRd: true)->rd()?->x();
```

The extension is sent to the API as given; the API ignores case and spaces. An empty
extension means no extension. `withPostcode('1076LJ', 12, 'H')` finds the separate
ground-floor home 12-H; the realtor suffix `hs` is not an extension and answers
`NotFoundException`.

When several homes share the postcode and house number and no extension is given, the API
answers with an `AddressAmbiguousException`. Its `candidates()` method returns the homes
as `Address` objects.

<a name="every-home-at-a-house-number"></a>
## Every home at a house number

`findAll()` returns every home at the address as `Results<Address>`:

```php
$homes = $client->addresses()
    ->withPostcode('1076LJ', 12)
    ->findAll();

foreach ($homes as $home) {
    $home->houseNumberExt();
}

$homes->meta()?->credits()?->charged();
```

<a name="autocomplete"></a>
## Autocomplete

`GET /v5/addresses/suggest` accepts a postcode, a postcode with house
number, a street, or a street with house number and city:

```php
$suggestions = $client->addresses()->suggest('1076LJ 12-2'); // Results<AddressSuggestion>

$suggestions->data()[0]->street();
$suggestions->data()[0]->city();
```

Limit the number of results (1 to 20) or the kinds of results:

```php
use Henzeb\Housepedia\Data\Intelligence\V5\Enums\SuggestionType;

$client->addresses()->suggest('Amazonenstraat', limit: 5, types: [SuggestionType::Street]);
```

<a name="reverse-geocoding"></a>
## Reverse geocoding

`GET /v5/addresses/reverse` returns the addresses closest to a coordinate (WGS84), nearest
first:

```php
$nearby = $client->addresses()->reverse(52.3453, 4.8627); // ReverseResults

$nearby->data()[0]->distanceInMeters();
```

The search radius in metres (1 to 200, default 50) and the number of results (1 to 10,
default 5) are optional:

```php
$client->addresses()->reverse(52.3453, 4.8627, radiusInMeters: 100, limit: 3);
```

Coordinates may also be strings, which are sent exactly as given. Floats are sent as the
shortest decimal that is exactly the same float. A string must be a decimal number with a
`.` (`'52.34536032'`, `'-4.8'`); anything else throws an `InvalidCoordinateException` before a
request is sent:

```php
$client->addresses()->reverse('52.34536032', '4.86274012');
```

`areas()` returns the neighbourhood, district, municipality and province of the coordinate:

```php
$nearby->areas()?->municipality()?->name();
```
