<?php

namespace Egmond\InertiaTables\Actions\Concerns;

use Egmond\InertiaTables\Actions\Contracts\CallbackAction;
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

    public function generateCallback(string $tableClass, array $recordIds = []): string
    {
        return URL::temporarySignedRoute('inertia-tables.action', now()->addMinutes(15), [
            'table' => base64_encode($tableClass),
            'name' => $this->getName(),
            'action' => base64_encode(static::class),
            'records' => $recordIds,
        ]);
    }

    public function getCallback(array $recordIds = []): string
    {
        $tableClass = $this->getTableClass();

        if (! $tableClass) {
            throw new \Exception('Table class must be set to generate frontend callback');
        }

        return $this->generateCallback($tableClass, $recordIds);
    }
}