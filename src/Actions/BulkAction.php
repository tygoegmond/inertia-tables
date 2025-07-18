<?php

namespace Egmond\InertiaTables\Actions;

class BulkAction extends Action
{
    protected bool $deselectRecordsAfterCompletion = true;

    public function deselectRecordsAfterCompletion(bool $condition = true): static
    {
        $this->deselectRecordsAfterCompletion = $condition;

        return $this;
    }

    public function shouldDeselectRecordsAfterCompletion(): bool
    {
        return $this->deselectRecordsAfterCompletion;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'deselectRecordsAfterCompletion' => $this->deselectRecordsAfterCompletion,
        ]);
    }
}
