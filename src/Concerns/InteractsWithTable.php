<?php

namespace Egmond\InertiaTables\Concerns;

use Egmond\InertiaTables\Table;
use Egmond\InertiaTables\TableResult;

trait InteractsWithTable
{
    protected ?Table $tableInstance = null;

    public static function make(): static
    {
        return new static;
    }

    public function as(string $name): static
    {
        $this->getTable()->as($name);

        return $this;
    }

    public function getTable(): Table
    {
        if (! $this->tableInstance) {
            $this->tableInstance = new Table;
            $this->tableInstance->setTableClass(static::class);
            $this->table($this->tableInstance);
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
