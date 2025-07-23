<?php

namespace Egmond\InertiaTables\Actions;

class Action extends BaseAction
{
    use Concerns\HasAction;
    use Concerns\HasUrl;

    protected function getAdditionalArrayData(): array
    {
        return [
            'openUrlInNewTab' => $this->shouldOpenUrlInNewTab(),
            'hasUrl' => $this->hasUrl(),
        ];
    }
}
