<?php

namespace Egmond\InertiaTables\Columns;

class IconColumn extends BaseColumn
{
    protected string $type = 'icon';
    protected array $icons = [];
    protected string $size = 'md';
    protected array $colors = [];
    protected ?string $defaultIcon = null;
    protected ?string $defaultColor = null;

    public function icons(array $icons): static
    {
        $this->icons = $icons;
        return $this;
    }

    public function size(string $size): static
    {
        $this->size = $size;
        return $this;
    }

    public function colors(array $colors): static
    {
        $this->colors = $colors;
        return $this;
    }

    public function defaultIcon(string $icon): static
    {
        $this->defaultIcon = $icon;
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
            'icon' => $this->icons[$value] ?? $this->defaultIcon ?? 'circle',
            'color' => $this->colors[$value] ?? $this->defaultColor ?? 'gray',
            'size' => $this->size,
        ];
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'icons' => $this->icons,
            'colors' => $this->colors,
            'size' => $this->size,
            'defaultIcon' => $this->defaultIcon,
            'defaultColor' => $this->defaultColor,
        ]);
    }
}