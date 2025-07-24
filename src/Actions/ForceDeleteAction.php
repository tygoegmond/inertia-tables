<?php

namespace Egmond\InertiaTables\Actions;

use Egmond\InertiaTables\Actions\Contracts\ArrayableAction;
use Egmond\InertiaTables\Actions\Contracts\CallbackAction;
use Egmond\InertiaTables\Actions\Contracts\ExecutableAction;
use Illuminate\Contracts\Support\Arrayable;

/** @phpstan-consistent-constructor */
class ForceDeleteAction extends AbstractAction implements ExecutableAction, ArrayableAction, CallbackAction, Arrayable
{
    use Concerns\ExecutesAction;
    use Concerns\HasCallback;
    use Concerns\SerializesToArray;

    public function __construct(string $name = 'force_delete')
    {
        parent::__construct($name);

        $this->label('Force Delete')
            ->requiresConfirmation()
            ->confirmationTitle('Confirm Force Deletion')
            ->confirmationMessage('Are you sure you want to permanently delete this record? This action cannot be undone and will bypass soft delete.')
            ->confirmationButton('Delete Permanently')
            ->cancelButton('Cancel')
            ->color('danger')
            ->action(fn ($record) => $record->forceDelete());
    }

    public static function make(string $name = 'force_delete'): static
    {
        return new static($name);
    }
}
