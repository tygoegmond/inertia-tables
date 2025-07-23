<?php

namespace Egmond\InertiaTables\Actions;

class ForceDeleteAction extends Action
{
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

    public static function make(string $name = 'force-delete'): static
    {
        return new static($name);
    }
}
