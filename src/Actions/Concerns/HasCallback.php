<?php

namespace Egmond\InertiaTables\Actions\Concerns;

use Illuminate\Support\Facades\URL;

trait HasCallback
{
    protected ?string $tableClass = null;

    public function setTableClass(string $tableClass): static
    {
        $this->tableClass = $tableClass;

        return $this;
    }

    public function getTableClass(): ?string
    {
        return $this->tableClass;
    }

    public function generateCallback(string $tableClass, ?string $recordKey = null): string
    {
        $params = [
            'table' => base64_encode($tableClass),
            'name' => $this->getName(),
            'action' => base64_encode(static::class),
        ];

        // For regular Actions: record parameter is required and included in signed URL
        if ($this instanceof \Egmond\InertiaTables\Actions\Action) {
            if ($recordKey === null) {
                throw new \InvalidArgumentException('Record key is required for regular actions');
            }
            $params['record'] = $recordKey;
        }

        // For BulkActions: record parameter should never be included in signed URL
        // (records will be sent in POST body and require authorization)

        return URL::temporarySignedRoute('inertia-tables.action', now()->addMinutes(15), $params);
    }

    public function getCallback(?string $recordKey = null): string
    {
        $tableClass = $this->getTableClass();

        if (! $tableClass) {
            throw new \Exception('Table class must be set to generate frontend callback');
        }

        return $this->generateCallback($tableClass, $recordKey);
    }
}
