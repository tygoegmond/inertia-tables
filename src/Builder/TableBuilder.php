<?php

namespace Egmond\InertiaTables\Builder;

use Egmond\InertiaTables\Columns\BaseColumn;
use Egmond\InertiaTables\TableResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class TableBuilder
{
    protected array $columns = [];

    protected int $perPage = 25;

    protected array $defaultSort = [];

    protected bool $searchable = false;

    protected ?Request $request = null;

    protected ?string $name = null;

    protected array $actions = [];

    protected array $bulkActions = [];

    protected array $headerActions = [];

    protected ?string $tableClass = null;

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

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function actions(array $actions): static
    {
        $this->actions = $actions;

        return $this;
    }

    public function bulkActions(array $bulkActions): static
    {
        $this->bulkActions = $bulkActions;

        return $this;
    }

    public function headerActions(array $headerActions): static
    {
        $this->headerActions = $headerActions;

        return $this;
    }

    public function setTableClass(string $tableClass): static
    {
        $this->tableClass = $tableClass;

        return $this;
    }

    public function build(Builder $query): TableResult
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
        $page = $this->getPageNumber();
        $results = $query->paginate($this->perPage, ['*'], 'page', $page);

        // Transform data
        $transformedData = $this->transformData($results);

        return new TableResult(
            config: $this->getConfig(),
            data: $transformedData,
            pagination: $this->getPaginationData($results),
            sort: $this->getSortData(),
            search: $this->getSearchQuery(),
            name: $this->name,
            actions: $this->serializeActions($this->actions),
            bulkActions: $this->serializeActions($this->bulkActions),
            headerActions: $this->serializeActions($this->headerActions),
            primaryKey: $this->primaryKey,
        );
    }

    protected function applySearch(Builder $query): Builder
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

    protected ?string $primaryKey = null;

    protected function transformData(LengthAwarePaginator $results): array
    {
        return $results->getCollection()->map(function ($record) {
            $recordArray = $record->toArray();
            $transformedRecord = [];
            $badgeVariants = [];

            // Always include the primary key for row identification
            $primaryKey = $record->getKeyName();
            $this->primaryKey = $primaryKey; // Store for TableResult
            $transformedRecord[$primaryKey] = $record->getKey();

            foreach ($this->columns as $column) {
                $key = $column->getKey();
                $value = data_get($record, $key);
                $transformedRecord[$key] = $column->formatValue($value, $recordArray);

                // Add badge variant to meta for TextColumn with badge enabled
                if ($column instanceof \Egmond\InertiaTables\Columns\TextColumn && $column->toArray()['badge']) {
                    $badgeVariants[$key] = $column->resolveBadgeVariant($value, $recordArray);
                }
            }

            // Add meta data if it has badge variants
            if (! empty($badgeVariants)) {
                $transformedRecord['meta'] = [
                    'badgeVariant' => $badgeVariants,
                ];
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
        $tableParams = $this->request->get($this->name, []);
        $sort = $tableParams['sort'] ?? null;

        if (is_string($sort)) {
            $direction = $tableParams['direction'] ?? 'asc';

            return [$sort => $direction];
        }

        return is_array($sort) ? $sort : [];
    }

    protected function getSearchQuery(): ?string
    {
        $tableParams = $this->request->get($this->name, []);

        return $tableParams['search'] ?? null;
    }

    protected function getPageNumber(): int
    {
        $tableParams = $this->request->get($this->name, []);

        return (int) ($tableParams['page'] ?? 1);
    }

    protected function applyRelationshipAggregations(Builder $query): Builder
    {
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

    protected function applyEagerLoading(Builder $query): Builder
    {
        $relationships = [];

        foreach ($this->columns as $column) {
            $key = $column->getKey();

            if (str_contains($key, '.')) {
                $relationshipName = explode('.', $key)[0];
                $relationships[] = $relationshipName;
            }
        }

        if (! empty($relationships)) {
            $query->with(array_unique($relationships));
        }

        return $query;
    }

    protected function applyCount(Builder $query, string|array $relationship): void
    {
        if (is_string($relationship)) {
            $query->withCount($relationship);
        } else {
            $query->withCount($relationship);
        }
    }

    protected function applyExists(Builder $query, string|array $relationship): void
    {
        if (is_string($relationship)) {
            $query->withExists($relationship);
        } else {
            $query->withExists($relationship);
        }
    }

    protected function applyAvg(Builder $query, string|array $relationship, string $column): void
    {
        if (is_string($relationship)) {
            $query->withAvg($relationship, $column);
        } else {
            $query->withAvg($relationship, $column);
        }
    }

    protected function applyMax(Builder $query, string|array $relationship, string $column): void
    {
        if (is_string($relationship)) {
            $query->withMax($relationship, $column);
        } else {
            $query->withMax($relationship, $column);
        }
    }

    protected function applyMin(Builder $query, string|array $relationship, string $column): void
    {
        if (is_string($relationship)) {
            $query->withMin($relationship, $column);
        } else {
            $query->withMin($relationship, $column);
        }
    }

    protected function applySum(Builder $query, string|array $relationship, string $column): void
    {
        if (is_string($relationship)) {
            $query->withSum($relationship, $column);
        } else {
            $query->withSum($relationship, $column);
        }
    }

    protected function serializeActions(array $actions): array
    {
        return array_map(function ($action) {
            if (method_exists($action, 'toArray')) {
                // Set table class for URL generation
                if (method_exists($action, 'setTableClass') && $this->tableClass) {
                    $action->setTableClass($this->tableClass);
                }

                $actionData = $action->toArray();
                $actionData['tableName'] = $this->name;

                return $actionData;
            }

            return $action;
        }, $actions);
    }
}
