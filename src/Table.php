<?php

namespace Egmond\InertiaTables;

use Illuminate\Database\Eloquent\Builder;

class Table
{
    protected array $columns = [];

    protected array $filters = [];

    protected array $actions = [];

    protected array $bulkActions = [];

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

    public function filters(array $filters): static
    {
        $this->filters = $filters;

        return $this;
    }

    public function actions(array $actions): static
    {
        $this->actions = $actions;

        return $this;
    }

    public function bulkActions(array $bulkActions): static
    {
        $this->bulkActions = $bulkActions;

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

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function getBulkActions(): array
    {
        return $this->bulkActions;
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
            ->filters($this->filters)
            ->searchable($this->searchable)
            ->paginate($this->perPage);

        foreach ($this->defaultSort as $column => $direction) {
            $builder->sortBy($column, $direction);
        }

        return $builder->build($this->query);
    }
}
