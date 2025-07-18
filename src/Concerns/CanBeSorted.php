<?php

namespace Egmond\InertiaTables\Concerns;

trait CanBeSorted
{
    protected bool $sortable = false;
    protected ?string $sortDirection = null;

    public function sortable(bool $sortable = true): static
    {
        $this->sortable = $sortable;
        return $this;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function defaultSort(string $direction = 'asc'): static
    {
        $this->sortDirection = $direction;
        return $this;
    }

    public function getDefaultSortDirection(): ?string
    {
        return $this->sortDirection;
    }
}