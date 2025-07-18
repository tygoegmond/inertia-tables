<?php

namespace Egmond\InertiaTables\Columns;

class ImageColumn extends BaseColumn
{
    protected string $type = 'image';

    protected ?string $fallback = null;

    protected string $size = 'md';

    protected bool $rounded = false;

    protected ?string $alt = null;

    public function fallback(string $fallback): static
    {
        $this->fallback = $fallback;

        return $this;
    }

    public function size(string $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function rounded(bool $rounded = true): static
    {
        $this->rounded = $rounded;

        return $this;
    }

    public function alt(string $alt): static
    {
        $this->alt = $alt;

        return $this;
    }

    public function formatValue(mixed $value, array $record): mixed
    {
        if ($value === null) {
            return null;
        }

        return [
            'src' => $value,
            'fallback' => $this->fallback,
            'size' => $this->size,
            'rounded' => $this->rounded,
            'alt' => $this->alt ?? 'Image',
        ];
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'fallback' => $this->fallback,
            'size' => $this->size,
            'rounded' => $this->rounded,
            'alt' => $this->alt,
        ]);
    }
}
