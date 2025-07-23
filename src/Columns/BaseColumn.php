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
        $data = [
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

        return $this->filterDefaults($data);
    }

    protected function filterDefaults(array $data): array
    {
        return array_filter($data, function ($value, $key) {
            // Always include required fields
            if (in_array($key, ['key', 'label', 'type'])) {
                return true;
            }
            
            // For visible field, only include if false (since true is default)
            if ($key === 'visible') {
                return $value === false;
            }
            
            // Filter out false, null, empty strings, and empty arrays for other fields
            return $value !== false && $value !== null && $value !== '' && $value !== [];
        }, ARRAY_FILTER_USE_BOTH);
    }

    protected function generateLabel(string $key): string
    {
        return ucfirst(str_replace(['_', '-'], ' ', $key));
    }
}
