<?php

namespace Egmond\InertiaTables\Filters;

use Illuminate\Database\Eloquent\Builder;

class SearchFilter extends BaseFilter
{
    protected string $type = 'search';

    protected array $columns = [];

    protected string $placeholder = 'Search...';

    public function columns(array $columns): static
    {
        $this->columns = $columns;

        return $this;
    }

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function apply(Builder $query, mixed $value): Builder
    {
        if (empty($value) || ! is_string($value)) {
            return $query;
        }

        $searchColumns = $this->columns ?: [$this->getKey()];

        return $query->where(function ($query) use ($searchColumns, $value) {
            foreach ($searchColumns as $column) {
                $query->orWhere($column, 'like', '%'.$value.'%');
            }
        });
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'columns' => $this->columns,
            'placeholder' => $this->placeholder,
        ]);
    }
}
