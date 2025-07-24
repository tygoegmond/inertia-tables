<?php

namespace Egmond\InertiaTables\Actions;

class BulkAction extends BaseAction
{
    use Concerns\HasBulkAction;

    public function __construct(string $name)
    {
        parent::__construct($name);
    }

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
            'actionUrl' => $this->isDisabled() ? null : $this->getActionUrl(),
        ];
    }
}
