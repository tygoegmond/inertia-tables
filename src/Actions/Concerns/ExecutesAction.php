<?php

namespace Egmond\InertiaTables\Actions\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

trait ExecutesAction
{
    protected ?Closure $action = null;

    public function action(Closure $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function hasAction(): bool
    {
        return $this->action !== null;
    }

    public function execute(...$parameters): mixed
    {
        if (! $this->action) {
            return null;
        }

        $firstParameter = $parameters[0] ?? null;

        if ($firstParameter instanceof Collection) {
            return $this->evaluate($this->action, ['records' => $firstParameter]);
        }

        if ($firstParameter instanceof Model || $firstParameter === null) {
            return $this->evaluate($this->action, ['record' => $firstParameter]);
        }

        return $this->evaluate($this->action, $parameters);
    }
}