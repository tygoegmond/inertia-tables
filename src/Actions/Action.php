<?php

namespace Egmond\InertiaTables\Actions;

class Action extends BaseAction
{
    use Concerns\HasAction;
    use Concerns\HasUrl;

    protected function getAdditionalArrayData(): array
    {
        return [
            'hasUrl' => $this->hasUrl(),
        ];
    }

    protected function getAdditionalRowData(?\Illuminate\Database\Eloquent\Model $record = null): array
    {
        return [
            'openUrlInNewTab' => $this->shouldOpenUrlInNewTab(),
        ];
    }
}
