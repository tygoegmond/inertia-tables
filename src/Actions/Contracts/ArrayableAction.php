<?php

namespace Egmond\InertiaTables\Actions\Contracts;

interface ArrayableAction
{
    public function toArray(): array;

    public function getStaticProperties(): array;

    public function toRowArray(?\Illuminate\Database\Eloquent\Model $record = null): array;
}