<?php

namespace Egmond\InertiaTables\Actions\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Model;

trait CanBeHidden
{
    protected bool|Closure $hidden = false;

    protected bool|Closure $visible = true;

    public function hidden(bool|Closure $condition = true): static
    {
        $this->hidden = $condition;

        return $this;
    }

    public function visible(bool|Closure $condition = true): static
    {
        $this->visible = $condition;

        return $this;
    }

    public function isHidden(?Model $record = null): bool
    {
        if ($this->hidden instanceof Closure) {
            return $this->evaluate($this->hidden, ['record' => $record]);
        }

        return $this->hidden;
    }

    public function isVisible(?Model $record = null): bool
    {
        if ($this->visible instanceof Closure) {
            $visible = $this->evaluate($this->visible, ['record' => $record]);
        } else {
            $visible = $this->visible;
        }

        return $visible && ! $this->isHidden($record);
    }
}
