<?php

namespace Egmond\InertiaTables\Filters;

use Illuminate\Database\Eloquent\Builder;

class NumberRangeFilter extends BaseFilter
{
    protected string $type = 'number_range';

    protected ?string $minPlaceholder = null;

    protected ?string $maxPlaceholder = null;

    protected ?float $minValue = null;

    protected ?float $maxValue = null;

    protected ?float $step = null;

    public function minPlaceholder(string $placeholder): static
    {
        $this->minPlaceholder = $placeholder;

        return $this;
    }

    public function maxPlaceholder(string $placeholder): static
    {
        $this->maxPlaceholder = $placeholder;

        return $this;
    }

    public function minValue(float $value): static
    {
        $this->minValue = $value;

        return $this;
    }

    public function maxValue(float $value): static
    {
        $this->maxValue = $value;

        return $this;
    }

    public function step(float $step): static
    {
        $this->step = $step;

        return $this;
    }

    public function apply(Builder $query, mixed $value): Builder
    {
        if (! is_array($value)) {
            return $query;
        }

        $min = $value['min'] ?? null;
        $max = $value['max'] ?? null;

        if ($min !== null && is_numeric($min)) {
            $query->where($this->getKey(), '>=', $min);
        }

        if ($max !== null && is_numeric($max)) {
            $query->where($this->getKey(), '<=', $max);
        }

        return $query;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'minPlaceholder' => $this->minPlaceholder ?? 'Min value',
            'maxPlaceholder' => $this->maxPlaceholder ?? 'Max value',
            'minValue' => $this->minValue,
            'maxValue' => $this->maxValue,
            'step' => $this->step,
        ]);
    }
}
