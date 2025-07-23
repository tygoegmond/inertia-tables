<?php

namespace Egmond\InertiaTables\Actions;

/** @phpstan-consistent-constructor */
class ReplicateAction extends Action
{
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
