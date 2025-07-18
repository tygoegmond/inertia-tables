<?php

namespace Egmond\InertiaTables\Concerns;

trait CanBeSearched
{
    protected bool $searchable = false;

    protected ?string $searchColumn = null;

    public function searchable(bool $searchable = true, ?string $column = null): static
    {
        $this->searchable = $searchable;
        $this->searchColumn = $column;

        return $this;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function getSearchColumn(): ?string
    {
        return $this->searchColumn ?? $this->getKey();
    }
}
