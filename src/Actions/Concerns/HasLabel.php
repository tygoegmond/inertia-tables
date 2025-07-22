<?php

namespace Egmond\InertiaTables\Actions\Concerns;

trait HasLabel
{
    protected ?string $label = null;

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label ?? $this->getDefaultLabel();
    }

    protected function getDefaultLabel(): string
    {
        return str($this->getName())->title()->replace(['_', '-'], ' ')->toString();
    }
}