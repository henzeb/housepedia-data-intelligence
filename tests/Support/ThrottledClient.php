<?php

namespace Tests\Support;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ThrottledClient implements ClientInterface
{
    private ?float $lastRequestAt = null;

    public function __construct(
        private readonly ClientInterface $client,
        private readonly int $intervalMs,
    ) {
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        if ($this->lastRequestAt !== null) {
            $wait = $this->lastRequestAt + $this->intervalMs / 1000 - microtime(true);

            if ($wait > 0) {
                usleep((int)($wait * 1_000_000));
            }
        }

        try {
            return $this->client->sendRequest($request);
        } finally {
            $this->lastRequestAt = microtime(true);
        }
    }
}
