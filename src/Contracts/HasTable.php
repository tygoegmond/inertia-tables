<?php

namespace Egmond\InertiaTables\Contracts;

use Egmond\InertiaTables\Table;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

interface HasTable extends Arrayable, JsonSerializable
{
    public function table(Table $table): Table;
}
