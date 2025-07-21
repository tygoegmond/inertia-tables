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

    public function wrap(string $wrap): static
    {
        $this->wrap = $wrap;

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
        return array_merge(parent::toArray(), [
            'prefix' => $this->prefix,
            'suffix' => $this->suffix,
            'copyable' => $this->copyable,
            'limit' => $this->limit,
            'wrap' => $this->wrap,
            'badge' => $this->badge,
            'badgeVariant' => $this->badgeVariant,
        ]);
    }
}
