<?php

namespace Egmond\InertiaTables\Actions\Contracts;

interface ExecutableAction
{
    public function hasAction(): bool;

    public function execute(...$parameters): mixed;
}
