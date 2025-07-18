<?php

namespace Egmond\InertiaTables\Columns;

class BadgeColumn extends BaseColumn
{
    protected string $type = 'badge';
    protected array $colors = [];
    protected string $size = 'md';
    protected string $variant = 'default';
    protected ?string $defaultColor = null;

    public function colors(array $colors): static
    {
        $this->colors = $colors;
        return $this;
    }

    public function size(string $size): static
    {
        $this->size = $size;
        return $this;
    }

    public function variant(string $variant): static
    {
        $this->variant = $variant;
        return $this;
    }

    public function defaultColor(string $color): static
    {
        $this->defaultColor = $color;
        return $this;
    }

    public function formatValue(mixed $value, array $record): mixed
    {
        if ($value === null) {
            return null;
        }

        return [
            'value' => $value,
            'color' => $this->colors[$value] ?? $this->defaultColor ?? 'gray',
            'size' => $this->size,
            'variant' => $this->variant,
        ];
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'colors' => $this->colors,
            'size' => $this->size,
            'variant' => $this->variant,
            'defaultColor' => $this->defaultColor,
        ]);
    }
}