<?php

namespace Henzeb\Housepedia\Data\Intelligence\V5\DTO;

use Henzeb\Housepedia\Data\Intelligence\Contracts\DTO\Area as AreaContract;
use Henzeb\Housepedia\Data\Intelligence\Support\Payload;

class Area implements AreaContract
{
    /**
     * @param list<AreaParent> $parents
     * @param array<string, mixed>|null $geometry
     */
    public function __construct(
        private string $type,
        private string $code,
        private ?string $housepediaId,
        private ?string $name,
        private ?string $slug,
        private array $parents,
        private ?Location $centroid,
        private ?int $cbsVintageYear,
        private ?array $geometry,
        private ?Meta $meta,
    ) {
    }

    public static function from(Payload $payload): self
    {
        return new self(
            $payload->requireString('type'),
            $payload->requireString('code'),
            $payload->string('huispedia_id'),
            $payload->string('name'),
            $payload->string('slug'),
            $payload->list('parents', AreaParent::from(...)),
            $payload->nested('centroid', Location::from(...)),
            $payload->integer('cbs_vintage_year'),
            $payload->associative('geometry'),
            $payload->nested('meta', Meta::from(...)),
        );
    }

    public function type(): string
    {
        return $this->type;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function housepediaId(): ?string
    {
        return $this->housepediaId;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function slug(): ?string
    {
        return $this->slug;
    }

    /**
     * @return list<AreaParent>
     */
    public function parents(): array
    {
        return $this->parents;
    }

    public function centroid(): ?Location
    {
        return $this->centroid;
    }

    public function cbsVintageYear(): ?int
    {
        return $this->cbsVintageYear;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function geometry(): ?array
    {
        return $this->geometry;
    }

    public function meta(): ?Meta
    {
        return $this->meta;
    }
}
