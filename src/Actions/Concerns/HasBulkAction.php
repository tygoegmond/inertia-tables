<?php

namespace Egmond\InertiaTables\Actions\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Collection;

/**
 * @deprecated Use ExecutesAction trait instead
 */
trait HasBulkAction
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

    public function execute(Collection $records): mixed
    {
        if (! $this->action) {
            return null;
        }

        return $this->evaluate($this->action, ['records' => $records]);
    }
}
