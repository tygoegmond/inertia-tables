<?php

namespace Egmond\InertiaTables\Actions;

/** @phpstan-consistent-constructor */
class RestoreAction extends Action
{
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
