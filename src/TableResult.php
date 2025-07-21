<?php

namespace Egmond\InertiaTables;

use Egmond\InertiaTables\Serialization\Serializer;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

class TableResult implements Arrayable, Jsonable, JsonSerializable
{
    public function __construct(
        public readonly array $config,
        public readonly array $data,
        public readonly array $pagination,
        public readonly array $sort = [],
        public readonly ?string $search = null,
    ) {}

    public function toArray(): array
    {
        return Serializer::serializeTableResult($this);
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
