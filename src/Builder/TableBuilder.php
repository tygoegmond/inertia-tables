<?php

namespace Egmond\InertiaTables\Builder;

use Egmond\InertiaTables\Columns\BaseColumn;
use Egmond\InertiaTables\Filters\BaseFilter;
use Egmond\InertiaTables\Serialization\Serializer;
use Egmond\InertiaTables\TableResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TableBuilder
{
    protected array $columns = [];
    protected array $filters = [];
    protected int $perPage = 25;
    protected array $defaultSort = [];
    protected bool $searchable = false;
    protected ?Request $request = null;

    public function __construct(?Request $request = null)
    {
        $this->request = $request ?? request();
    }

    public static function make(?Request $request = null): static
    {
        return new static($request);
    }

    public function columns(array $columns): static
    {
        foreach ($columns as $column) {
            $this->addColumn($column);
        }
        return $this;
    }

    public function addColumn(BaseColumn $column): static
    {
        $this->columns[$column->getKey()] = $column;
        return $this;
    }

    public function filters(array $filters): static
    {
        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }
        return $this;
    }

    public function addFilter(BaseFilter $filter): static
    {
        $this->filters[$filter->getKey()] = $filter;
        return $this;
    }

    public function paginate(int $perPage): static
    {
        $this->perPage = $perPage;
        return $this;
    }

    public function sortBy(string $column, string $direction = 'asc'): static
    {
        $this->defaultSort[$column] = $direction;
        return $this;
    }

    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;
        return $this;
    }

    public function build(Builder $query): TableResult
    {
        // Apply search
        $query = $this->applySearch($query);

        // Apply filters
        $query = $this->applyFilters($query);

        // Apply sorting
        $query = $this->applySorting($query);

        // Execute query with pagination
        $results = $query->paginate($this->perPage);

        // Transform data
        $transformedData = $this->transformData($results);

        return new TableResult(
            config: $this->getConfig(),
            data: $transformedData,
            pagination: $this->getPaginationData($results),
            filters: $this->getFilterData(),
            sort: $this->getSortData(),
            search: $this->getSearchQuery(),
        );
    }

    protected function applySearch(Builder $query): Builder
    {
        $search = $this->getSearchQuery();
        
        if (!$search || !$this->searchable) {
            return $query;
        }

        $searchableColumns = collect($this->columns)
            ->filter(fn($column) => $column->isSearchable())
            ->keys()
            ->toArray();

        if (empty($searchableColumns)) {
            return $query;
        }

        return $query->where(function ($query) use ($searchableColumns, $search) {
            foreach ($searchableColumns as $column) {
                $query->orWhere($column, 'like', '%' . $search . '%');
            }
        });
    }

    protected function applyFilters(Builder $query): Builder
    {
        $filterValues = $this->getFilterValues();

        foreach ($this->filters as $filter) {
            $value = $filterValues[$filter->getKey()] ?? null;
            
            if ($value !== null && $value !== '') {
                $query = $filter->apply($query, $value);
            }
        }

        return $query;
    }

    protected function applySorting(Builder $query): Builder
    {
        $sortData = $this->getSortData();

        if (empty($sortData)) {
            $sortData = $this->defaultSort;
        }

        foreach ($sortData as $column => $direction) {
            if (isset($this->columns[$column]) && $this->columns[$column]->isSortable()) {
                $query->orderBy($column, $direction);
            }
        }

        return $query;
    }

    protected function transformData(LengthAwarePaginator $results): array
    {
        return $results->getCollection()->map(function ($record) {
            $recordArray = $record->toArray();
            $transformedRecord = [];

            foreach ($this->columns as $column) {
                $key = $column->getKey();
                $value = data_get($recordArray, $key);
                $transformedRecord[$key] = $column->formatValue($value, $recordArray);
            }

            return $transformedRecord;
        })->toArray();
    }

    protected function getConfig(): array
    {
        return [
            'columns' => array_map(fn($column) => $column->toArray(), $this->columns),
            'filters' => array_map(fn($filter) => $filter->toArray(), $this->filters),
            'searchable' => $this->searchable,
            'perPage' => $this->perPage,
            'defaultSort' => $this->defaultSort,
        ];
    }

    protected function getPaginationData(LengthAwarePaginator $results): array
    {
        return [
            'current_page' => $results->currentPage(),
            'per_page' => $results->perPage(),
            'total' => $results->total(),
            'last_page' => $results->lastPage(),
            'from' => $results->firstItem(),
            'to' => $results->lastItem(),
            'links' => $results->linkCollection()->toArray(),
        ];
    }

    protected function getFilterData(): array
    {
        return $this->getFilterValues();
    }

    protected function getSortData(): array
    {
        $sort = $this->request->get('sort', []);
        
        if (is_string($sort)) {
            $direction = $this->request->get('direction', 'asc');
            return [$sort => $direction];
        }

        return is_array($sort) ? $sort : [];
    }

    protected function getSearchQuery(): ?string
    {
        return $this->request->get('search');
    }

    protected function getFilterValues(): array
    {
        return $this->request->get('filters', []);
    }
}