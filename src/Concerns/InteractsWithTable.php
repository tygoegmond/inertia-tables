<?php

namespace Egmond\InertiaTables\Concerns;

use Closure;
use Egmond\InertiaTables\Table;
use Egmond\InertiaTables\TableResult;
use Illuminate\Database\Eloquent\Builder;

trait InteractsWithTable
{
    protected ?Table $tableInstance = null;

    protected Builder|Closure|null $query = null;

    public static function make(): static
    {
        return new static;
    }

    public function query(Builder|Closure $query): static
    {
        $this->query = $query;

        return $this;
    }

    public function getTable(): Table
    {
        if (! $this->tableInstance) {
            $this->tableInstance = new Table;
            $this->table($this->tableInstance);

            if ($this->query !== null) {
                $this->tableInstance->query($this->query);
            }
        }

        return $this->tableInstance;
    }

    public function getTableResult(): TableResult
    {
        return $this->getTable()->build();
    }

    public function toArray(): array
    {
        return $this->getTableResult()->toArray();
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    abstract public function table(Table $table): Table;
}
