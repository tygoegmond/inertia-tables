<?php

namespace Egmond\InertiaTables\Actions\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Model;

trait HasAuthorization
{
    protected ?Closure $authorization = null;

    public function authorize(Closure $callback): static
    {
        $this->authorization = $callback;

        return $this;
    }

    public function isAuthorized(?Model $record = null): bool
    {
        if (! $this->authorization) {
            return true;
        }

        return $this->evaluate($this->authorization, ['record' => $record]);
    }
}