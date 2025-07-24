<?php

namespace Egmond\InertiaTables\Actions\Contracts;

interface CallbackAction
{
    public function getCallback(string $recordKey = null): string;

    public function generateCallback(string $tableClass, string $recordKey = null): string;

    public function setTableClass(string $tableClass): static;

    public function getTableClass(): ?string;
}
