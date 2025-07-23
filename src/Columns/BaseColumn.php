<?php

namespace Egmond\InertiaTables\Columns;

use Egmond\InertiaTables\Concerns\CanBeSearched;
use Egmond\InertiaTables\Concerns\CanBeSorted;
use Egmond\InertiaTables\Concerns\HasRelationship;
use Egmond\InertiaTables\Concerns\HasState;
use Egmond\InertiaTables\Contracts\HasLabel;

/** @phpstan-consistent-constructor */
abstract class BaseColumn
{
    use CanBeSearched, CanBeSorted, HasRelationship, HasState;

    protected string $type;

    public function __construct(string $key)
    {
        $this->key = $key;
        $this->label = $this->generateLabel($key);
    }

    public static function make(string $key): static
    {
        return new static($key);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function formatValue(mixed $value, array $record): mixed
    {
        return $this->formatEnum($value);
    }

    protected function formatEnum(mixed $value): mixed
    {
        if ($value instanceof HasLabel) {
            return $value->getLabel();
        }

        // If it's an enum but doesn't implement HasLabel, return the backing value
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        return $value;
    }

    public function toArray(): array
    {
        return [
            'key' => $this->getKey(),
            'label' => $this->getLabel(),
            'type' => $this->getType(),
            'visible' => $this->isVisible(),
            'sortable' => $this->isSortable(),
            'searchable' => $this->isSearchable(),
            'searchColumn' => $this->getSearchColumn(),
            'defaultSort' => $this->getDefaultSortDirection(),
            'state' => $this->getState(),
        ];
    }

    protected function generateLabel(string $key): string
    {
        return ucfirst(str_replace(['_', '-'], ' ', $key));
    }
}
