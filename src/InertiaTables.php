<?php

namespace Egmond\InertiaTables;

use Egmond\InertiaTables\Builder\TableBuilder;
use Illuminate\Http\Request;

class InertiaTables
{
    public static function table(?Request $request = null): TableBuilder
    {
        return TableBuilder::make($request);
    }
}
