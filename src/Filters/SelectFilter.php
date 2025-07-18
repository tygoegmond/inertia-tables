<?php

namespace Egmond\InertiaTables\Filters;

use Illuminate\Database\Eloquent\Builder;

class SelectFilter extends BaseFilter
{
    protected string $type = 'select';

    protected array $options = [];

    protected ?string $placeholder = null;

    protected bool $multiple = false;

    public function options(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function apply(Builder $query, mixed $value): Builder
    {
        if (empty($value)) {
            return $query;
        }

        if ($this->multiple && is_array($value)) {
            return $query->whereIn($this->getKey(), $value);
        }

        return $query->where($this->getKey(), $value);
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'options' => $this->options,
            'placeholder' => $this->placeholder,
            'multiple' => $this->multiple,
        ]);
    }
}
