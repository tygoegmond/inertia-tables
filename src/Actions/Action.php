<?php

namespace Egmond\InertiaTables\Actions;

use Egmond\InertiaTables\Actions\Contracts\ArrayableAction;
use Egmond\InertiaTables\Actions\Contracts\CallbackAction;
use Egmond\InertiaTables\Actions\Contracts\ExecutableAction;
use Illuminate\Contracts\Support\Arrayable;

class Action extends AbstractAction implements ExecutableAction, ArrayableAction, CallbackAction, Arrayable
{
    use Concerns\ExecutesAction;
    use Concerns\HasCallback;
    use Concerns\SerializesToArray;
}
