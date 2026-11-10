# Errors

Every exception extends `Henzeb\Housepedia\Data\Intelligence\Exceptions\HousepediaException`.

## API errors

Error responses of the API throw an `ApiException` subclass:

| HTTP | `errorCode()`                                              | Exception                     |
|------|------------------------------------------------------------|-------------------------------|
| 400  | `invalid_request`                                          | `ValidationException`         |
| 401  | `unauthenticated`, `key_revoked`, `key_expired`            | `AuthenticationException`     |
| 402  | `insufficient_credits`, `spend_cap_reached`                | `PaymentRequiredException`    |
| 403  | `entitlement_required`, `ip_not_allowed`, `account_suspended`, `forbidden` | `ForbiddenException` |
| 404  | `not_found`                                                | `NotFoundException`           |
| 409  | `address_ambiguous`                                        | `AddressAmbiguousException`   |
| 409  | `conflict`                                                 | `ConflictException`           |
| 422  | `data_unavailable`                                         | `DataUnavailableException`    |
| 429  | `rate_limited`                                             | `RateLimitException`          |
| 503  | `upstream_unavailable`, `billing_unavailable`              | `ServiceUnavailableException` |
| 5xx  | `internal_error`                                           | `ServerException`             |
| 405 and others | `method_not_allowed`, …                          | `ApiException`                |

`AddressAmbiguousException` extends `ConflictException`.

```php
use Henzeb\Housepedia\Data\Intelligence\Exceptions\ApiException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\RateLimitException;

try {
    $client->properties()->withPostcode('1076LJ', 12, '2')->valuation();
} catch (RateLimitException $exception) {
    $exception->retryAfter(); // seconds, from the Retry-After header, after the last retry
} catch (ApiException $exception) {
    $exception->status();     // 404
    $exception->errorCode();  // not_found
    $exception->title();
    $exception->detail();     // in the language of the client
    $exception->requestId();
    $exception->errors();     // per field for a 400, the candidate homes for a 409
}
```

Exceptions have no `meta()`: `requestId()` is the id of the failed
call, and `retryAfter()` says when to try again after a 429.

## Retrying

A `RateLimitException` (429) is retried twice by default. Between attempts the client waits
the seconds of `retryAfter()`, or one second when the API sends none. When the last attempt
fails, the exception is thrown. The number of retries and retrying a
`ServiceUnavailableException` (503) are set in the `Config`:

```php
use Henzeb\Housepedia\Data\Intelligence\Client;
use Henzeb\Housepedia\Data\Intelligence\Config;

$client = Client::create($apiKey, config: new Config(retries: 5, retryServiceUnavailable: true));
$client = Client::create($apiKey, config: new Config(retries: 0));   // never retry
```

`Client::retryUsing()` replaces how requests are retried, for every client. The callback
receives a closure that sends the request once, and the `Config` of the client:

```php
use Closure;
use Henzeb\Housepedia\Data\Intelligence\Config;
use Henzeb\Housepedia\Data\Intelligence\Response;

Client::retryUsing(function (Closure $attempt, Config $config): Response {
    for ($retries = 0; ; $retries++) {
        try {
            return $attempt();
        } catch (Throwable $exception) {
            if ($retries >= $config->retries() || ! $config->shouldRetry($exception)) {
                throw $exception;
            }

            usleep($config->retryDelay($exception) * 1_000_000);
        }
    }
});

Client::retryUsing(null);   // the default again
```

## Other exceptions

| Exception                         | Thrown when                                                    |
|-----------------------------------|----------------------------------------------------------------|
| `MissingApiKeyException`          | An endpoint that needs an API key is called without one. No request is sent. |
| `MissingLocatorException`         | An endpoint is called before a locator is set.                 |
| `InvalidCoordinateException`      | A coordinate string is not a decimal number such as `52.34536`, or a coordinate float is `NAN` or infinite. No request is sent. |
| `TransportException`              | The HTTP client fails to send the request.                     |
| `UnexpectedResponseTypeException` | A response field is missing or has a type the SDK does not expect. |
