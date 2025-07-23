<?php

namespace Egmond\InertiaTables\Actions\Concerns;

use Illuminate\Support\Facades\URL;

trait GeneratesActionUrls
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

    public function generateActionUrl(string $tableClass, array $recordIds = []): string
    {
        return URL::temporarySignedRoute('inertia-tables.action', now()->addMinutes(15), [
            'table' => base64_encode($tableClass),
            'name' => $this->getName(),
            'action' => base64_encode(static::class),
            'records' => $recordIds,
        ]);
    }

    public function getActionUrl(array $recordIds = []): string
    {
        $tableClass = $this->getTableClass();

        if (! $tableClass) {
            throw new \Exception('Table class must be set to generate action URL');
        }

        return $this->generateActionUrl($tableClass, $recordIds);
    }
}
