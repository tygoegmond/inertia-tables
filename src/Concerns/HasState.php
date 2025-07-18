<?php

namespace Egmond\InertiaTables\Concerns;

trait HasState
{
    protected string $key;
    protected string $label;
    protected bool $visible = true;
    protected array $state = [];

    public function getKey(): string
    {
        return $this->key;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function visible(bool $visible = true): static
    {
        $this->visible = $visible;
        return $this;
    }

    public function hidden(): static
    {
        return $this->visible(false);
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function state(array $state): static
    {
        $this->state = array_merge($this->state, $state);
        return $this;
    }

    public function getState(): array
    {
        return $this->state;
    }
}