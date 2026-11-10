# Raw Requests

`get()` and `post()` are the low-level request methods: they send a request to the path
you give and return a `Response` with the decoded JSON body. They use the same
authentication, base URL and [error handling](errors.md) as the typed methods.

The typed methods return typed objects and build paths and queries for you. Use `get()`
and `post()` for endpoints that have no typed method yet, such as endpoints the API added
after this release.

```php
use Henzeb\Housepedia\Data\Intelligence\Locators\Locator;

$locator = Locator::postcode('1076LJ', 12, '2');

$response = $client->get("/v5/properties/{$locator}/parcel", ['include' => 'rd']);
$response = $client->post('/v5/batch/valuations', ['data' => [['locator' => $locator->value()]]]);
```

Query values may be strings, integers, floats, booleans or `null`; `null` values are left out.

## Reading a response

```php
$response->status();                         // 200
$response->headers();                        // array<string, list<string>>
$response->header('X-RateLimit-Remaining');  // ?string
$response->meta();                           // ?Meta
$response->rateLimit();                      // ?RateLimit
$response->raw();                            // the decoded JSON body

$response->string('data.0.street');          // ?string
$response->integer('valuation.value');       // ?int
$response->float('address.location.lat');    // ?float
$response->boolean('is_residential');        // ?bool
$response->array('data');                    // ?array
$response->has('meta.next_cursor');          // bool
```

The typed readers take a key in dot notation. They return `null` when the key is missing
and throw an `UnexpectedResponseTypeException` when the value has another type.

`Locator::value()` returns the locator for a JSON body; casting the locator to a string
returns it encoded for a URL path.
