<?php

namespace Egmond\InertiaTables\Actions\Contracts;

interface CallbackAction
{
    public function getCallback(array $recordIds = []): string;

    public function generateCallback(string $tableClass, array $recordIds = []): string;

    public function setTableClass(string $tableClass): static;

    public function getTableClass(): ?string;
}
