<?php

namespace Egmond\InertiaTables\Actions\Concerns;

trait HasSize
{
    protected string $size = 'sm';

    public function size(string $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function small(): static
    {
        return $this->size('sm');
    }

    public function medium(): static
    {
        return $this->size('md');
    }

    public function large(): static
    {
        return $this->size('lg');
    }

    public function getSize(): string
    {
        return $this->size;
    }
}