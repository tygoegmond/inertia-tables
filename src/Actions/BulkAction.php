<?php

namespace Egmond\InertiaTables\Actions;

use Egmond\InertiaTables\Actions\Contracts\ArrayableAction;
use Egmond\InertiaTables\Actions\Contracts\CallbackAction;
use Egmond\InertiaTables\Actions\Contracts\ExecutableAction;
use Illuminate\Contracts\Support\Arrayable;

class BulkAction extends AbstractAction implements Arrayable, ArrayableAction, CallbackAction, ExecutableAction
{
    use Concerns\ExecutesAction;
    use Concerns\HasCallback;
    use Concerns\SerializesToArray;

    public function isAuthorized(?\Illuminate\Database\Eloquent\Model $record = null): bool
    {
        if (! $this->authorization) {
            throw new \Exception("BulkAction '{$this->name}' must have an authorize() method defined for security purposes.");
        }

        return parent::isAuthorized($record);
    }

    protected function getAdditionalArrayData(): array
    {
        return [
            'disabled' => $this->isDisabled(),
            'callback' => $this->isDisabled() ? null : $this->getCallback(),
        ];
    }
}
