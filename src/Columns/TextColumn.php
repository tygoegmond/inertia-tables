<?php

namespace Egmond\InertiaTables\Columns;

class TextColumn extends BaseColumn
{
    protected string $type = 'text';

    protected ?string $prefix = null;

    protected ?string $suffix = null;

    protected bool $copyable = false;

    protected ?int $limit = null;

    protected string $wrap = 'truncate';

    protected bool $badge = false;

    protected string|\Closure|null $badgeVariant = null;

    public function prefix(string $prefix): static
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function suffix(string $suffix): static
    {
        $this->suffix = $suffix;

        return $this;
    }

    public function copyable(bool $copyable = true): static
    {
        $this->copyable = $copyable;

        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    public function wrap(): static
    {
        $this->wrap = 'break-words';

        return $this;
    }

    public function badge(bool $badge = true): static
    {
        $this->badge = $badge;

        return $this;
    }

    public function badgeVariant(string|\Closure $badgeVariant): static
    {
        $this->badgeVariant = $badgeVariant;

        return $this;
    }

    public function isBadgeEnabled(): bool
    {
        return $this->badge;
    }

    public function formatValue(mixed $value, array $record): mixed
    {
        if ($value === null) {
            return null;
        }

        // Let parent handle formatting first
        $value = parent::formatValue($value, $record);

        $formatted = (string) $value;

        if ($this->limit && strlen($formatted) > $this->limit) {
            $formatted = substr($formatted, 0, $this->limit).'...';
        }

        if ($this->prefix) {
            $formatted = $this->prefix.$formatted;
        }

        if ($this->suffix) {
            $formatted = $formatted.$this->suffix;
        }

        return $formatted;
    }

    public function resolveBadgeVariant(mixed $value, array $record): ?string
    {
        if ($this->badgeVariant === null) {
            return 'default';
        }

        if ($this->badgeVariant instanceof \Closure) {
            return call_user_func($this->badgeVariant, $value, $record);
        }

        return $this->badgeVariant;
    }

    public function toArray(): array
    {
        // Build raw data without filtering from parent
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
            'prefix' => $this->prefix,
            'suffix' => $this->suffix,
            'copyable' => $this->copyable,
            'limit' => $this->limit,
            'wrap' => $this->wrap,
            'badge' => $this->badge,
        ];

        return $this->filterDefaults($data);
    }

    protected function filterDefaults(array $data): array
    {
        return array_filter($data, function ($value, $key) {
            // Always include required fields and wrap (React depends on explicit wrap values)
            if (in_array($key, ['key', 'label', 'type', 'wrap'])) {
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
}
