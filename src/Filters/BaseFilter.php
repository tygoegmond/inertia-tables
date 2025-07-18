<?php

namespace Egmond\InertiaTables\Filters;

use Egmond\InertiaTables\Concerns\HasState;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseFilter
{
    use HasState;

    protected string $type;
    protected mixed $defaultValue = null;

    public function __construct(string $key)
    {
        $this->key = $key;
        $this->label = $this->generateLabel($key);
    }

    public static function make(string $key): static
    {
        return new static($key);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function default(mixed $value): static
    {
        $this->defaultValue = $value;
        return $this;
    }

    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    abstract public function apply(Builder $query, mixed $value): Builder;

    public function toArray(): array
    {
        return [
            'key' => $this->getKey(),
            'label' => $this->getLabel(),
            'type' => $this->getType(),
            'visible' => $this->isVisible(),
            'defaultValue' => $this->getDefaultValue(),
            'state' => $this->getState(),
        ];
    }

    protected function generateLabel(string $key): string
    {
        return ucfirst(str_replace(['_', '-'], ' ', $key));
    }
}