<?php

namespace Egmond\InertiaTables\Actions;

/** @phpstan-consistent-constructor */
class DeleteAction extends Action
{
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
