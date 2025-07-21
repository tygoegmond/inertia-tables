<?php

namespace Egmond\InertiaTables\Builder;

use Egmond\InertiaTables\Columns\BaseColumn;
use Egmond\InertiaTables\TableResult;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class TableBuilder
{
    protected array $columns = [];

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

    public function build(EloquentBuilder|Builder $query): TableResult
    {
        // Apply relationship aggregations
        $query = $this->applyRelationshipAggregations($query);

        // Apply eager loading for relationship columns
        $query = $this->applyEagerLoading($query);

        // Apply search
        $query = $this->applySearch($query);

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
            sort: $this->getSortData(),
            search: $this->getSearchQuery(),
        );
    }

    protected function applySearch(EloquentBuilder|Builder $query): EloquentBuilder|Builder
    {
        $search = $this->getSearchQuery();

        if (! $search || ! $this->searchable) {
            return $query;
        }

        $searchableColumns = collect($this->columns)
            ->filter(fn ($column) => $column->isSearchable())
            ->keys()
            ->toArray();

        if (empty($searchableColumns)) {
            return $query;
        }

        return $query->where(function ($query) use ($searchableColumns, $search) {
            foreach ($searchableColumns as $column) {
                $query->orWhere($column, 'like', '%'.$search.'%');
            }
        });
    }

    protected function applySorting(EloquentBuilder|Builder $query): EloquentBuilder|Builder
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
            'columns' => array_values(array_map(fn ($column) => $column->toArray(), $this->columns)),
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

    protected function applyRelationshipAggregations(EloquentBuilder|Builder $query): EloquentBuilder|Builder
    {
        // Relationship aggregations are only supported for Eloquent Builder
        if (! $query instanceof EloquentBuilder) {
            return $query;
        }

        foreach ($this->columns as $column) {
            if ($column->hasRelationship()) {
                $relationship = $column->getRelationship();
                $type = $column->getRelationshipType();
                $relationshipColumn = $column->getRelationshipColumn();

                match ($type) {
                    'count' => $this->applyCount($query, $relationship),
                    'exists' => $this->applyExists($query, $relationship),
                    'avg' => $this->applyAvg($query, $relationship, $relationshipColumn),
                    'max' => $this->applyMax($query, $relationship, $relationshipColumn),
                    'min' => $this->applyMin($query, $relationship, $relationshipColumn),
                    'sum' => $this->applySum($query, $relationship, $relationshipColumn),
                    default => null,
                };
            }
        }

        return $query;
    }

    protected function applyEagerLoading(EloquentBuilder|Builder $query): EloquentBuilder|Builder
    {
        $relationships = [];

        foreach ($this->columns as $column) {
            $key = $column->getKey();

            if (str_contains($key, '.')) {
                $relationshipName = explode('.', $key)[0];
                $relationships[] = $relationshipName;
            }
        }

        if (! empty($relationships) && $query instanceof EloquentBuilder) {
            $query->with(array_unique($relationships));
        }

        return $query;
    }

    protected function applyCount(EloquentBuilder $query, string|array $relationship): void
    {
        if (is_string($relationship)) {
            $query->withCount($relationship);
        } else {
            $query->withCount($relationship);
        }
    }

    protected function applyExists(EloquentBuilder $query, string|array $relationship): void
    {
        if (is_string($relationship)) {
            $query->withExists($relationship);
        } else {
            $query->withExists($relationship);
        }
    }

    protected function applyAvg(EloquentBuilder $query, string|array $relationship, string $column): void
    {
        if (is_string($relationship)) {
            $query->withAvg($relationship, $column);
        } else {
            $query->withAvg($relationship, $column);
        }
    }

    protected function applyMax(EloquentBuilder $query, string|array $relationship, string $column): void
    {
        if (is_string($relationship)) {
            $query->withMax($relationship, $column);
        } else {
            $query->withMax($relationship, $column);
        }
    }

    protected function applyMin(EloquentBuilder $query, string|array $relationship, string $column): void
    {
        if (is_string($relationship)) {
            $query->withMin($relationship, $column);
        } else {
            $query->withMin($relationship, $column);
        }
    }

    protected function applySum(EloquentBuilder $query, string|array $relationship, string $column): void
    {
        if (is_string($relationship)) {
            $query->withSum($relationship, $column);
        } else {
            $query->withSum($relationship, $column);
        }
    }
}
