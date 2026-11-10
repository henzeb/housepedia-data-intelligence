<?php

namespace Henzeb\Housepedia\Data\Intelligence;

use Henzeb\Housepedia\Data\Intelligence\Contracts\Api;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\ApiException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\RateLimitException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\ServiceUnavailableException;
use Henzeb\Housepedia\Data\Intelligence\Exceptions\UnsupportedVersionException;
use Henzeb\Housepedia\Data\Intelligence\Transport\Transport;
use Henzeb\Housepedia\Data\Intelligence\V5\Api as V5Api;
use Psr\SimpleCache\CacheInterface;
use Throwable;

class Config
{
    public const string FALLBACK_VERSION = 'V5';

    public const array VERSIONS = ['V5'];

    /**
     * @var value-of<self::VERSIONS>
     */
    private string $version;

    public function __construct(
        private ?string $baseUrl = null,
        private ?string $language = null,
        private int $retries = 2,
        private bool $retryServiceUnavailable = false,
        private ?CacheInterface $cache = null,
        private string $cachePrefix = 'housepedia-data-intelligence',
        private ?int $maxCacheAgeInSeconds = null,
        int|string|null $version = null,
    ) {
        $this->version = $version === null ? self::FALLBACK_VERSION : $this->normalise($version);
    }

    public function version(): string
    {
        return $this->version;
    }

    public function withVersion(int|string $version): static
    {
        $config = clone $this;
        $config->version = $this->normalise($version);

        return $config;
    }

    public function api(): Api
    {
        return match ($this->version) {
            'V5' => new V5Api(),
        };
    }

    public function baseUrl(): string
    {
        return $this->baseUrl === null || $this->baseUrl === '' ? Transport::BASE_URL : $this->baseUrl;
    }

    public function language(): ?string
    {
        return $this->language === '' ? null : $this->language;
    }

    public function retries(): int
    {
        return $this->retries;
    }

    public function retryServiceUnavailable(): bool
    {
        return $this->retryServiceUnavailable;
    }

    public function cache(): ?CacheInterface
    {
        return $this->cache;
    }

    public function cachePrefix(): string
    {
        return $this->cachePrefix;
    }

    public function maxCacheAgeInSeconds(): ?int
    {
        return $this->maxCacheAgeInSeconds;
    }

    public function withMaxCacheAge(int $seconds): static
    {
        $config = clone $this;
        $config->maxCacheAgeInSeconds = $seconds;

        return $config;
    }

    public function withoutCache(): static
    {
        $config = clone $this;
        $config->cache = null;

        return $config;
    }

    public function shouldRetry(Throwable $exception): bool
    {
        return $exception instanceof RateLimitException
            || ($this->retryServiceUnavailable && $exception instanceof ServiceUnavailableException);
    }

    public function retryDelay(Throwable $exception): int
    {
        return $exception instanceof ApiException ? ($exception->retryAfter() ?? 1) : 1;
    }

    /**
     * @return value-of<self::VERSIONS>
     */
    private function normalise(int|string $version): string
    {
        $normalised = preg_match('/^[vV]?(\d+)$/', (string)$version, $matches) === 1 ? 'V' . $matches[1] : null;

        if ($normalised === null || !in_array($normalised, self::VERSIONS, true)) {
            throw new UnsupportedVersionException($version, self::VERSIONS);
        }

        return $normalised;
    }
}
