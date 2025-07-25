<?php

namespace Egmond\InertiaTables\Actions\Contracts;

interface ActionContract
{
    public function getName(): string;

    public function getLabel(): string;

    public function isAuthorized(?\Illuminate\Database\Eloquent\Model $record = null): bool;

    public function isDisabled(?\Illuminate\Database\Eloquent\Model $record = null): bool;

    public function isHidden(?\Illuminate\Database\Eloquent\Model $record = null): bool;

    public function isVisible(?\Illuminate\Database\Eloquent\Model $record = null): bool;
}
