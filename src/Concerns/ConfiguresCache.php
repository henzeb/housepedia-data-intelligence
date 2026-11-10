<?php

namespace Henzeb\Housepedia\Data\Intelligence\Concerns;

trait ConfiguresCache
{
    public function withMaxCacheAge(int $seconds): static
    {
        $copy = clone $this;
        $copy->transport = $this->transport->withConfig($this->transport->config()->withMaxCacheAge($seconds));

        return $copy;
    }

    public function withoutCache(): static
    {
        $copy = clone $this;
        $copy->transport = $this->transport->withConfig($this->transport->config()->withoutCache());

        return $copy;
    }
}
