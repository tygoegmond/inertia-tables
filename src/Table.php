<?php

namespace Egmond\InertiaTables;

use Illuminate\Database\Eloquent\Builder;

class Table
{
    protected array $columns = [];

    protected ?Builder $query = null;

    protected int $perPage = 25;

    protected array $defaultSort = [];

    protected bool $searchable = false;

    public function query(Builder $query): static
    {
        $this->query = $query;

        return $this;
    }

    public function columns(array $columns): static
    {
        $this->columns = $columns;

        return $this;
    }

    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;

        return $this;
    }

    public function paginate(int $perPage): static
    {
        $this->perPage = $perPage;

        return $this;
    }

    public function defaultSort(string $column, string $direction = 'asc'): static
    {
        $this->defaultSort[$column] = $direction;

        return $this;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getQuery(): ?Builder
    {
        return $this->query;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getDefaultSort(): array
    {
        return $this->defaultSort;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function build(): TableResult
    {
        if (! $this->query) {
            throw new \Exception('Query is required. Use query() method to set the query.');
        }

        $builder = InertiaTables::table()
            ->columns($this->columns)
            ->searchable($this->searchable)
            ->paginate($this->perPage);

        foreach ($this->defaultSort as $column => $direction) {
            $builder->sortBy($column, $direction);
        }

        return $builder->build($this->query);
    }
}
