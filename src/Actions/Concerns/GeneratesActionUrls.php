<?php

namespace Egmond\InertiaTables\Actions\Concerns;

use Illuminate\Support\Facades\URL;

trait GeneratesActionUrls
{
    public function generateActionUrl(string $tableName, array $recordIds = []): string
    {
        return URL::signedRoute('inertia-tables.action', [
            'table' => base64_encode(get_class($this->getTable() ?? new \stdClass)),
            'action' => $this->getName(),
            'records' => $recordIds,
        ]);
    }

    public function getActionUrl(array $recordIds = []): string
    {
        if (! $this->getTable()) {
            throw new \Exception('Table must be set to generate action URL');
        }

        return $this->generateActionUrl($this->getTable()->getName(), $recordIds);
    }
}
