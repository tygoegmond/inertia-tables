<?php

namespace Egmond\InertiaTables\Actions;

use Egmond\InertiaTables\Actions\Contracts\ArrayableAction;
use Egmond\InertiaTables\Actions\Contracts\CallbackAction;
use Egmond\InertiaTables\Actions\Contracts\ExecutableAction;
use Illuminate\Contracts\Support\Arrayable;

/** @phpstan-consistent-constructor */
class RestoreAction extends AbstractAction implements ExecutableAction, ArrayableAction, CallbackAction, Arrayable
{
    use Concerns\ExecutesAction;
    use Concerns\HasCallback;
    use Concerns\SerializesToArray;

    public function __construct(string $name = 'restore')
    {
        parent::__construct($name);

        $this->label('Restore')
            ->color('success')
            ->action(fn ($record) => $record->restore());
    }

    public static function make(string $name = 'restore'): static
    {
        return new static($name);
    }
}
