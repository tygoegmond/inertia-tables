<?php

namespace Egmond\InertiaTables;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class Table
{
    protected array $columns = [];

    protected ?Builder $query = null;

    protected int $perPage = 25;

    protected array $defaultSort = [];

    protected bool $searchable = false;

    protected ?string $name = null;

    protected array $actions = [];

    protected array $bulkActions = [];

    protected array $headerActions = [];

    public function query(Builder|Closure $query): static
    {
        if ($query instanceof Closure) {
            $this->query = $query($this->query);
        } else {
            $this->query = $query;
        }

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

    public function as(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
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

    public function headerActions(array $headerActions): static
    {
        $this->headerActions = $headerActions;

        return $this;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function getBulkActions(): array
    {
        return $this->bulkActions;
    }

    public function getHeaderActions(): array
    {
        return $this->headerActions;
    }

    public function hasActions(): bool
    {
        return ! empty($this->actions);
    }

    public function hasBulkActions(): bool
    {
        return ! empty($this->bulkActions);
    }

    public function hasHeaderActions(): bool
    {
        return ! empty($this->headerActions);
    }

    public function build(): TableResult
    {
        if (! $this->query) {
            throw new \Exception('Query is required. Use query() method to set the query.');
        }

        if (! $this->name) {
            throw new \Exception('Table name is required. Use as() method to set the table name.');
        }

        $builder = InertiaTables::table()
            ->columns($this->columns)
            ->searchable($this->searchable)
            ->paginate($this->perPage);

        $builder->setName($this->name);

        foreach ($this->defaultSort as $column => $direction) {
            $builder->sortBy($column, $direction);
        }

        return $builder->build($this->query);
    }
}
