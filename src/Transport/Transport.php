<?php

namespace Henzeb\Housepedia\Data\Intelligence\Transport;

use Closure;
use Composer\InstalledVersions;
use Henzeb\Housepedia\Data\Intelligence\Config;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\AddressAmbiguousException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\ApiException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\AuthenticationException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\ConflictException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\DataUnavailableException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\ForbiddenException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\MissingApiKeyException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\NotFoundException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\PaymentRequiredException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\RateLimitException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\ServerException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\ServiceUnavailableException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\TransportException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\UnexpectedResponseTypeException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\ValidationException;
use Henzeb\Housepedia\Data\Intelligence\Response;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Transport
{
    public const string BASE_URL = 'https://huispedia.dev';

    public const string USER_AGENT = 'henzeb/housepedia-data-intelligence';

    /**
     * @var (Closure(Closure(): Response, Config): Response)|null
     */
    private static ?Closure $retryUsing = null;

    public function __construct(
        private ?string $apiKey,
        private ClientInterface $client,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
        private Config $config = new Config(),
    ) {
    }

    public function config(): Config
    {
        return $this->config;
    }

    public function withConfig(Config $config): static
    {
        $transport = clone $this;
        $transport->config = $config;

        return $transport;
    }

    /**
     * @param (Closure(Closure(): Response, Config): Response)|null $callback
     */
    public static function retryUsing(?Closure $callback): void
    {
        self::$retryUsing = $callback;
    }

    /**
     * @param array<string, string|int|float|bool|null> $query
     */
    public function get(string $path, array $query = [], bool $authenticate = true): Response
    {
        return $this->send($this->request('GET', $path, $query, null, $authenticate));
    }

    /**
     * @param array<string, string|int|float|bool|null> $query
     */
    public function head(string $path, array $query = []): Response
    {
        return $this->send($this->request('HEAD', $path, $query, null, true));
    }

    /**
     * @param array<array-key, mixed> $body
     * @param array<string, string|int|float|bool|null> $query
     */
    public function post(string $path, array $body, array $query = []): Response
    {
        return $this->send($this->request('POST', $path, $query, $body, true));
    }

    private function send(RequestInterface $request): Response
    {
        $cache = $this->config->cache();

        if ($cache === null || !in_array($request->getMethod(), ['GET', 'HEAD'], true)) {
            return $this->sendUncached($request);
        }

        $key = $this->cacheKey($request);
        $cached = $this->cachedResponse($cache->get($key));

        if ($cached !== null) {
            return $cached;
        }

        $response = $this->sendUncached($request);
        $timeToLive = $this->timeToLive($response);

        if ($timeToLive > 0) {
            $cache->set($key, ['status' => $response->status(), 'headers' => $this->cacheableHeaders($response), 'data' => $response->raw()], $timeToLive);
        }

        return $response;
    }

    private function sendUncached(RequestInterface $request): Response
    {
        $retry = self::$retryUsing ?? self::retry(...);

        return $retry(fn (): Response => $this->attempt($request), $this->config);
    }

    private function cacheKey(RequestInterface $request): string
    {
        return $this->config->cachePrefix() . '.' . hash('sha256', implode("\n", [
            $request->getMethod(),
            (string)$request->getUri(),
            $request->getHeaderLine('Accept-Language'),
            $this->apiKey ?? '',
        ]));
    }

    /**
     * @return array<string, list<string>>
     */
    private function cacheableHeaders(Response $response): array
    {
        return array_filter(
            $response->headers(),
            static fn (string $name): bool => stripos($name, 'X-RateLimit-') !== 0,
            ARRAY_FILTER_USE_KEY,
        );
    }

    private function cachedResponse(mixed $value): ?Response
    {
        if (!is_array($value) || !is_int($value['status'] ?? null) || !is_array($value['headers'] ?? null) || !is_array($value['data'] ?? null)) {
            return null;
        }

        $headers = [];

        foreach ($value['headers'] as $name => $values) {
            if (!is_array($values)) {
                return null;
            }

            $list = [];

            foreach ($values as $header) {
                if (!is_string($header)) {
                    return null;
                }

                $list[] = $header;
            }

            $headers[(string)$name] = $list;
        }

        return new Response($value['status'], $headers, $value['data']);
    }

    private function timeToLive(Response $response): int
    {
        if ($response->status() < 200 || $response->status() >= 300) {
            return 0;
        }

        $directives = [];

        foreach ($response->headers() as $name => $values) {
            if (strcasecmp($name, 'Cache-Control') === 0) {
                foreach (explode(',', implode(',', $values)) as $directive) {
                    $directives[] = strtolower(trim($directive));
                }
            }
        }

        if (in_array('no-store', $directives, true) || in_array('no-cache', $directives, true)) {
            return 0;
        }

        $maxAge = 0;

        foreach ($directives as $directive) {
            if (preg_match('/^max-age=(\d+)$/', $directive, $matches) === 1) {
                $maxAge = (int)$matches[1];
            }
        }

        $limit = $this->config->maxCacheAgeInSeconds();

        return $limit === null ? $maxAge : min($maxAge, $limit);
    }

    /**
     * @param Closure(): Response $attempt
     */
    private static function retry(Closure $attempt, Config $config): Response
    {
        for ($retries = 0; ; $retries++) {
            try {
                return $attempt();
            } catch (ApiException $exception) {
                if ($retries >= $config->retries() || !$config->shouldRetry($exception)) {
                    throw $exception;
                }

                sleep($config->retryDelay($exception));
            }
        }
    }

    private function attempt(RequestInterface $request): Response
    {
        try {
            $response = $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $exception) {
            throw new TransportException($exception->getMessage(), 0, $exception);
        }

        if ($response->getStatusCode() >= 400) {
            throw $this->exception($response);
        }

        return $this->response($response);
    }

    /**
     * @param array<string, string|int|float|bool|null> $query
     * @param array<array-key, mixed>|null $body
     */
    private function request(string $method, string $path, array $query, ?array $body, bool $authenticate): RequestInterface
    {
        $request = $this->requestFactory
            ->createRequest($method, $this->uri($path, $query))
            ->withHeader('Accept', 'application/json')
            ->withHeader('User-Agent', self::USER_AGENT . '/' . $this->version());

        if ($authenticate) {
            if ($this->apiKey === null || $this->apiKey === '') {
                throw new MissingApiKeyException($method, $path);
            }

            $request = $request->withHeader('Authorization', 'Bearer ' . $this->apiKey);
        }

        $language = $this->config->language();

        if ($language !== null) {
            $request = $request->withHeader('Accept-Language', $language);
        }

        if ($body !== null) {
            $request = $request
                ->withHeader('Content-Type', 'application/json')
                ->withBody(
                    $this->streamFactory->createStream(
                        json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION),
                    ),
                );
        }

        return $request;
    }

    private function version(): string
    {
        if (!InstalledVersions::isInstalled(self::USER_AGENT)) {
            return 'unknown';
        }

        return InstalledVersions::getPrettyVersion(self::USER_AGENT) ?? 'unknown';
    }

    /**
     * @param array<string, string|int|float|bool|null> $query
     */
    private function uri(string $path, array $query): string
    {
        $parameters = [];

        foreach ($query as $name => $value) {
            if ($value === null) {
                continue;
            }

            $parameters[$name] = match (true) {
                is_bool($value) => $value ? 'true' : 'false',
                is_float($value) => $this->decimal($value),
                default => (string)$value,
            };
        }

        $uri = rtrim($this->config->baseUrl(), '/') . '/' . ltrim($path, '/');

        if ($parameters === []) {
            return $uri;
        }

        return $uri . (str_contains($uri, '?') ? '&' : '?') . http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
    }

    private function decimal(float $value): string
    {
        if (!is_finite($value)) {
            return is_nan($value) ? 'NAN' : ($value > 0 ? 'INF' : '-INF');
        }

        for ($decimals = 0; $decimals <= 53; $decimals++) {
            $text = sprintf('%.' . $decimals . 'F', $value);

            if ((float)$text === $value) {
                return $text;
            }
        }

        return sprintf('%.17G', $value);
    }

    private function response(ResponseInterface $response): Response
    {
        $data = $this->decode($response);

        if ($data === null) {
            throw UnexpectedResponseTypeException::body($response->getStatusCode());
        }

        return new Response($response->getStatusCode(), $this->headers($response), $data);
    }

    /**
     * @return array<array-key, mixed>|null
     */
    private function decode(ResponseInterface $response): ?array
    {
        $body = (string)$response->getBody();

        if (trim($body) === '') {
            return [];
        }

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return is_array($data) ? $data : null;
    }

    /**
     * @return array<string, list<string>>
     */
    private function headers(ResponseInterface $response): array
    {
        $headers = [];

        foreach ($response->getHeaders() as $name => $values) {
            $headers[(string)$name] = array_values($values);
        }

        return $headers;
    }

    private function exception(ResponseInterface $response): ApiException
    {
        $status = $response->getStatusCode();
        $problem = new Response($status, $this->headers($response), $this->decode($response) ?? []);
        $code = $problem->string('code') ?? 'http_' . $status;

        $class = match (true) {
            $status === 400 => ValidationException::class,
            $status === 401 => AuthenticationException::class,
            $status === 402 => PaymentRequiredException::class,
            $status === 403 => ForbiddenException::class,
            $status === 404 => NotFoundException::class,
            $status === 409 && $code === 'address_ambiguous' => AddressAmbiguousException::class,
            $status === 409 => ConflictException::class,
            $status === 422 => DataUnavailableException::class,
            $status === 429 => RateLimitException::class,
            $status === 503 => ServiceUnavailableException::class,
            $status >= 500 => ServerException::class,
            default => ApiException::class,
        };

        $arguments = [
            $status,
            $code,
            $problem->string('title') ?? ($response->getReasonPhrase() ?: 'HTTP ' . $status),
            $problem->string('detail'),
            $problem->string('request_id'),
            $problem->payload()->arrays('errors'),
            $problem->string('type'),
            $this->retryAfter($problem->header('Retry-After')),
        ];

        if ($class === AddressAmbiguousException::class) {
            return new AddressAmbiguousException(...[...$arguments, null, $this->config->api()]);
        }

        return new $class(...$arguments);
    }

    private function retryAfter(?string $value): ?int
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        if (ctype_digit(trim($value))) {
            return (int)trim($value);
        }

        $timestamp = strtotime($value);

        return $timestamp === false ? null : max(0, $timestamp - time());
    }
}
