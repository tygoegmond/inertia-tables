<?php

namespace Egmond\InertiaTables\Actions\Concerns;

use Illuminate\Database\Eloquent\Model;

trait SerializesToArray
{
    public function toArray(): array
    {
        return $this->getStaticProperties();
    }

    public function getStaticProperties(): array
    {
        $data = [
            'name' => $this->getName(),
            'label' => $this->getLabel(),
            'color' => $this->getColor(),
            'requiresConfirmation' => $this->needsConfirmation(),
            'confirmationTitle' => $this->getConfirmationTitle(),
            'confirmationMessage' => $this->getConfirmationMessage(),
            'confirmationButton' => $this->getConfirmationButton(),
            'cancelButton' => $this->getCancelButton(),
            'hasAction' => $this->hasAction(),
        ];

        return $this->filterDefaults(array_merge($data, $this->getAdditionalArrayData()));
    }

    public function toRowArray(?Model $record = null): array
    {
        $isDisabled = $this->isDisabled($record);

        $data = [
            'disabled' => $isDisabled,
        ];

        if (! $isDisabled && method_exists($this, 'getCallback')) {
            $data['callback'] = $this->getCallback($record ? [$record->getKey()] : []);
        }

        return $this->filterDefaults(array_merge($data, $this->getAdditionalRowData($record)));
    }

    protected function filterDefaults(array $data): array
    {
        return array_filter($data, function ($value, $key) {
            if (in_array($key, ['name', 'label', 'color'])) {
                return true;
            }

            return $value !== false && $value !== null && $value !== '' && $value !== [];
        }, ARRAY_FILTER_USE_BOTH);
    }

    protected function getAdditionalArrayData(): array
    {
        return [];
    }

    protected function getAdditionalRowData(?Model $record = null): array
    {
        return [];
    }
}
