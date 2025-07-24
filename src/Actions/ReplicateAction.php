<?php

namespace Egmond\InertiaTables\Actions;

use Egmond\InertiaTables\Actions\Contracts\ArrayableAction;
use Egmond\InertiaTables\Actions\Contracts\CallbackAction;
use Egmond\InertiaTables\Actions\Contracts\ExecutableAction;
use Illuminate\Contracts\Support\Arrayable;

/** @phpstan-consistent-constructor */
class ReplicateAction extends AbstractAction implements ExecutableAction, ArrayableAction, CallbackAction, Arrayable
{
    use Concerns\ExecutesAction;
    use Concerns\HasCallback;
    use Concerns\SerializesToArray;

    public function __construct(string $name = 'replicate')
    {
        parent::__construct($name);

        $this->label('Duplicate')
            ->color('secondary')
            ->action(fn ($record) => $record->replicate()->save());
    }

    public static function make(string $name = 'replicate'): static
    {
        return new static($name);
    }
}
