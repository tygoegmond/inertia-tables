<?php

namespace Egmond\InertiaTables\Actions\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Model;

trait CanBeDisabled
{
    protected bool|Closure $disabled = false;

    public function disabled(bool|Closure $condition = true): static
    {
        $this->disabled = $condition;

        return $this;
    }

    public function isDisabled(?Model $record = null): bool
    {
        if ($this->disabled instanceof Closure) {
            return $this->evaluate($this->disabled, ['record' => $record]);
        }

        return $this->disabled;
    }
}