<?php

namespace Egmond\InertiaTables\Actions;

class BulkActionGroup
{
    protected array $actions = [];

    public function __construct(array $actions = [])
    {
        $this->actions = $actions;
    }

    public static function make(array $actions = []): static
    {
        return new static($actions);
    }

    public function actions(array $actions): static
    {
        $this->actions = $actions;
        return $this;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function toArray(): array
    {
        return [
            'actions' => array_map(fn($action) => $action->toArray(), $this->actions),
        ];
    }
}