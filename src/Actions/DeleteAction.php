<?php

namespace Egmond\InertiaTables\Actions;

use Egmond\InertiaTables\Actions\Contracts\ArrayableAction;
use Egmond\InertiaTables\Actions\Contracts\CallbackAction;
use Egmond\InertiaTables\Actions\Contracts\ExecutableAction;
use Illuminate\Contracts\Support\Arrayable;

/** @phpstan-consistent-constructor */
class DeleteAction extends AbstractAction implements ExecutableAction, ArrayableAction, CallbackAction, Arrayable
{
    use Concerns\ExecutesAction;
    use Concerns\HasCallback;
    use Concerns\SerializesToArray;

    public function __construct(string $name = 'delete')
    {
        parent::__construct($name);

        $this->label('Delete')
            ->requiresConfirmation()
            ->confirmationTitle('Confirm Deletion')
            ->confirmationMessage('Are you sure you want to delete this record? This action cannot be undone.')
            ->confirmationButton('Delete')
            ->cancelButton('Cancel')
            ->color('danger')
            ->action(fn ($record) => $record->delete());
    }

    public static function make(string $name = 'delete'): static
    {
        return new static($name);
    }
}
