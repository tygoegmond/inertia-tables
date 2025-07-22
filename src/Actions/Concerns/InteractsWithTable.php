<?php

namespace Egmond\InertiaTables\Actions\Concerns;

use Closure;
use Egmond\InertiaTables\Table;

trait InteractsWithTable
{
    protected ?Table $table = null;

    public function table(Table $table): static
    {
        $this->table = $table;

        return $this;
    }

    public function getTable(): ?Table
    {
        return $this->table;
    }

    protected function evaluate(Closure $closure, array $parameters = []): mixed
    {
        return $closure(...array_values($parameters));
    }
}