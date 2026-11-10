<?php

namespace Henzeb\Housepedia\Data\Intelligence;

use Closure;
use Henzeb\Housepedia\Data\Intelligence\Concerns\ConfiguresCache;
use Henzeb\Housepedia\Data\Intelligence\Contracts\Account;
use Henzeb\Housepedia\Data\Intelligence\Contracts\Addresses;
use Henzeb\Housepedia\Data\Intelligence\Contracts\Areas;
use Henzeb\Housepedia\Data\Intelligence\Contracts\Buildings;
use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Catalog\Catalog;
use Henzeb\Housepedia\Data\Intelligence\Contracts\Properties;
use Henzeb\Housepedia\Data\Intelligence\Contracts\Valuations;
use Henzeb\Housepedia\Data\Intelligence\Transport\Transport;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;

class Client
{
    use ConfiguresCache;

    public function __construct(
        private Transport $transport,
    ) {
    }

    public static function create(
        ?string $apiKey = null,
        ?ClientInterface $httpClient = null,
        Config $config = new Config(),
    ): self {
        return new self(
            new Transport(
                $apiKey,
                $httpClient ?? Psr18ClientDiscovery::find(),
                Psr17FactoryDiscovery::findRequestFactory(),
                Psr17FactoryDiscovery::findStreamFactory(),
                $config,
            ),
        );
    }

    /**
     * @param (Closure(Closure(): Response, Config): Response)|null $callback
     */
    public static function retryUsing(?Closure $callback): void
    {
        Transport::retryUsing($callback);
    }

    public function version(): string
    {
        return $this->transport->config()->version();
    }

    public function withVersion(int|string $version): static
    {
        $copy = clone $this;
        $copy->transport = $this->transport->withConfig($this->transport->config()->withVersion($version));

        return $copy;
    }

    public function addresses(): Addresses
    {
        return $this->transport->config()->api()->addresses($this->transport);
    }

    public function properties(): Properties
    {
        return $this->transport->config()->api()->properties($this->transport);
    }

    public function buildings(): Buildings
    {
        return $this->transport->config()->api()->buildings($this->transport);
    }

    public function areas(): Areas
    {
        return $this->transport->config()->api()->areas($this->transport);
    }

    public function valuations(): Valuations
    {
        return $this->transport->config()->api()->valuations($this->transport);
    }

    public function account(): Account
    {
        return $this->transport->config()->api()->account($this->transport);
    }

    public function catalog(): Catalog
    {
        return $this->transport->config()->api()->catalog($this->transport);
    }

    /**
     * @param array<string, string|int|float|bool|null> $query
     */
    public function get(string $path, array $query = []): Response
    {
        return $this->transport->get($path, $query);
    }

    /**
     * @param array<array-key, mixed> $body
     * @param array<string, string|int|float|bool|null> $query
     */
    public function post(string $path, array $body, array $query = []): Response
    {
        return $this->transport->post($path, $body, $query);
    }
}
