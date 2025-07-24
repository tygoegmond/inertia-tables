<?php

namespace Egmond\InertiaTables\Actions;

class Action extends BaseAction
{
    use Concerns\HasAction;

    public function toRowArray(?\Illuminate\Database\Eloquent\Model $record = null): array
    {
        $isDisabled = $this->isDisabled($record);

        $data = [
            'disabled' => $isDisabled,
        ];

        // Only generate actionUrl if the action is not disabled
        if (! $isDisabled) {
            $data['actionUrl'] = $this->getActionUrl($record ? [$record->getKey()] : []);
        }

        return $this->filterDefaults(array_merge($data, $this->getAdditionalRowData($record)));
    }

    protected function getAdditionalRowData(?\Illuminate\Database\Eloquent\Model $record = null): array
    {
        return [];
    }
}
