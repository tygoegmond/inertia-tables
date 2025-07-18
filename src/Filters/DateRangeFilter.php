<?php

namespace Egmond\InertiaTables\Filters;

use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class DateRangeFilter extends BaseFilter
{
    protected string $type = 'date_range';
    protected ?string $format = null;
    protected ?string $startPlaceholder = null;
    protected ?string $endPlaceholder = null;
    protected ?Carbon $minDate = null;
    protected ?Carbon $maxDate = null;

    public function format(string $format): static
    {
        $this->format = $format;
        return $this;
    }

    public function startPlaceholder(string $placeholder): static
    {
        $this->startPlaceholder = $placeholder;
        return $this;
    }

    public function endPlaceholder(string $placeholder): static
    {
        $this->endPlaceholder = $placeholder;
        return $this;
    }

    public function minDate(Carbon $date): static
    {
        $this->minDate = $date;
        return $this;
    }

    public function maxDate(Carbon $date): static
    {
        $this->maxDate = $date;
        return $this;
    }

    public function apply(Builder $query, mixed $value): Builder
    {
        if (!is_array($value)) {
            return $query;
        }

        $start = $value['start'] ?? null;
        $end = $value['end'] ?? null;

        if ($start) {
            $query->whereDate($this->getKey(), '>=', Carbon::parse($start));
        }

        if ($end) {
            $query->whereDate($this->getKey(), '<=', Carbon::parse($end));
        }

        return $query;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'format' => $this->format ?? 'Y-m-d',
            'startPlaceholder' => $this->startPlaceholder ?? 'Start date',
            'endPlaceholder' => $this->endPlaceholder ?? 'End date',
            'minDate' => $this->minDate?->format('Y-m-d'),
            'maxDate' => $this->maxDate?->format('Y-m-d'),
        ]);
    }
}