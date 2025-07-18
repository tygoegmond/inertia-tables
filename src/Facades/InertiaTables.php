<?php

namespace Egmond\InertiaTables\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Egmond\InertiaTables\InertiaTables
 */
class InertiaTables extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Egmond\InertiaTables\InertiaTables::class;
    }
}
