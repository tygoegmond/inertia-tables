<?php

namespace Egmond\InertiaTables\Actions\Concerns;

trait HasColor
{
    protected string $color = 'primary';

    public function color(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function danger(): static
    {
        return $this->color('danger');
    }

    public function success(): static
    {
        return $this->color('success');
    }

    public function warning(): static
    {
        return $this->color('warning');
    }

    public function info(): static
    {
        return $this->color('info');
    }

    public function gray(): static
    {
        return $this->color('gray');
    }

    public function primary(): static
    {
        return $this->color('primary');
    }

    public function getColor(): string
    {
        return $this->color;
    }
}
