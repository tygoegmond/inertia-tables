---
title: Advanced Usage
description: Advanced patterns, performance optimization, and complex use cases for Inertia Tables.
---

## Overview

This guide covers advanced usage patterns, performance optimization techniques, and complex scenarios for building sophisticated data tables with Inertia Tables.

## Performance Optimization

### Database Query Optimization

#### Efficient Eager Loading

```php
public function table(Table $table): Table
{
    return $table->as('posts')
        ->query(
            Post::query()
                ->with(['author', 'category']) // Eager load relationships
                ->withCount(['comments', 'likes']) // Aggregate counts
                ->select(['posts.*']) // Only select needed columns
        )
        ->columns([
            TextColumn::make('title')->searchable(),
            TextColumn::make('author.name')->sortable(),
            TextColumn::make('comments_count')->sortable(),
        ]);
}
```

#### Database Indexes for Performance

```php
// In your migration
Schema::table('posts', function (Blueprint $table) {
    // Index for sorting
    $table->index('created_at');
    $table->index('title');
    
    // Composite indexes for multi-column operations
    $table->index(['status', 'created_at']);
    $table->index(['author_id', 'created_at']);
    
    // Full-text search index
    $table->fullText(['title', 'content']);
});
```

#### Optimized Search Queries

```php
protected function applySearch(Builder $query): Builder
{
    $search = $this->getSearchQuery();
    
    if (!$search || !$this->searchable) {
        return $query;
    }
    
    // Use full-text search for better performance
    if (strlen($search) > 2) {
        return $query->whereRaw(
            "MATCH(title, content) AGAINST(? IN BOOLEAN MODE)",
            ["+{$search}*"]
        );
    }
    
    // Fallback to LIKE for short queries
    return $query->where(function ($query) use ($search) {
        $query->where('title', 'like', "{$search}%")
              ->orWhere('content', 'like', "{$search}%");
    });
}
```

### Memory Management

#### Chunked Processing for Large Datasets

```php
public function exportAllAction()
{
    $filename = 'large_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
    $path = storage_path('app/exports/' . $filename);
    
    $file = fopen($path, 'w');
    fputcsv($file, ['ID', 'Name', 'Email', 'Created At']);
    
    // Process in chunks to avoid memory issues
    User::chunk(1000, function ($users) use ($file) {
        foreach ($users as $user) {
            fputcsv($file, [
                $user->id,
                $user->name,
                $user->email,
                $user->created_at->toDateString(),
            ]);
        }
    });
    
    fclose($file);
    
    return response()->download($path)->deleteFileAfterSend();
}
```

#### Cursor Pagination for Large Tables

```php
public function table(Table $table): Table
{
    $query = User::query()->orderBy('id');
    
    // Use cursor pagination for better performance
    if (request('cursor')) {
        $query->where('id', '>', request('cursor'));
    }
    
    $results = $query->limit(50)->get();
    
    return $table
        ->query($query)
        ->customPagination($results); // Custom pagination implementation
}
```

## Advanced Table Patterns

### Multi-Tenant Tables

```php
<?php

namespace App\Tables;

use App\Models\User;
use Egmond\InertiaTables\Table;

class TenantAwareUserTable implements HasTable
{
    use InteractsWithTable;
    
    protected ?string $tenantId = null;
    
    public function __construct(?string $tenantId = null)
    {
        $this->tenantId = $tenantId ?? auth()->user()->tenant_id;
    }
    
    public static function forTenant(string $tenantId): static
    {
        return new static($tenantId);
    }
    
    public function table(Table $table): Table
    {
        return $table->as('tenant_users')
            ->query(
                User::query()
                    ->where('tenant_id', $this->tenantId)
                    ->with(['roles', 'permissions'])
            )
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('roles.name')->label('Role'),
            ])
            ->actions([
                Action::make('edit')
                    ->authorize(fn($record) => 
                        auth()->user()->can('update', $record) && 
                        $record->tenant_id === $this->tenantId
                    ),
            ]);
    }
}
```

### Hierarchical Data Tables

```php
public function table(Table $table): Table
{
    return $table->as('categories')
        ->query(
            Category::query()
                ->with('parent')
                ->withCount('children')
                ->orderBy('parent_id')
                ->orderBy('sort_order')
        )
        ->columns([
            TextColumn::make('hierarchical_name')
                ->formatStateUsing(function ($state, $record) {
                    $indent = str_repeat('—', $record->depth);
                    return $indent . ' ' . $record->name;
                }),
            TextColumn::make('children_count')->label('Subcategories'),
        ])
        ->actions([
            Action::make('add_child')
                ->label('Add Subcategory')
                ->visible(fn($record) => $record->depth < 3),
        ]);
}
```

### Dynamic Column Configuration

```php
<?php

namespace App\Tables;

class ConfigurableUserTable implements HasTable
{
    use InteractsWithTable;
    
    protected array $visibleColumns;
    
    public function __construct(array $visibleColumns = null)
    {
        $this->visibleColumns = $visibleColumns ?? $this->getDefaultColumns();
    }
    
    public function table(Table $table): Table
    {
        $allColumns = [
            'name' => TextColumn::make('name')->searchable()->sortable(),
            'email' => TextColumn::make('email')->searchable()->sortable(),
            'phone' => TextColumn::make('phone')->searchable(),
            'company' => TextColumn::make('company')->searchable(),
            'created_at' => TextColumn::make('created_at')->sortable(),
            'last_login' => TextColumn::make('last_login_at')->sortable(),
        ];
        
        $columns = collect($allColumns)
            ->filter(fn($column, $key) => in_array($key, $this->visibleColumns))
            ->values()
            ->toArray();
        
        return $table->as('configurable_users')
            ->query(User::query())
            ->columns($columns);
    }
    
    public function saveColumnConfigAction()
    {
        $columns = request('visible_columns', []);
        
        auth()->user()->update([
            'table_preferences->users->visible_columns' => $columns,
        ]);
        
        return back()->with('success', 'Column configuration saved');
    }
    
    protected function getDefaultColumns(): array
    {
        return auth()->user()->table_preferences['users']['visible_columns'] ?? 
               ['name', 'email', 'created_at'];
    }
}
```

## Integration Patterns

### API-First Tables

```php
<?php

namespace App\Tables;

class ApiBackedTable implements HasTable
{
    use InteractsWithTable;
    
    protected string $apiEndpoint;
    protected array $apiParams;
    
    public function __construct(string $endpoint, array $params = [])
    {
        $this->apiEndpoint = $endpoint;
        $this->apiParams = $params;
    }
    
    public function table(Table $table): Table
    {
        $data = $this->fetchFromApi();
        
        return $table->as('api_data')
            ->customData($data) // Custom data source
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('status'),
            ]);
    }
    
    protected function fetchFromApi(): array
    {
        $response = Http::get($this->apiEndpoint, array_merge(
            $this->apiParams,
            [
                'search' => request('search'),
                'page' => request('page', 1),
                'per_page' => 25,
            ]
        ));
        
        return $response->json();
    }
}
```

### Event-Driven Tables

```php
<?php

namespace App\Tables;

use App\Events\TableActionExecuted;
use Illuminate\Support\Facades\Event;

class EventDrivenTable implements HasTable
{
    use InteractsWithTable;
    
    public function deleteAction($record)
    {
        Event::dispatch(new TableActionExecuted('delete', 'users', $record));
        
        $record->delete();
        
        // Log the action
        activity()
            ->performedOn($record)
            ->causedBy(auth()->user())
            ->log('User deleted from table');
        
        return back()->with('success', 'User deleted successfully');
    }
    
    public function bulkDeleteBulkAction($records)
    {
        $deletedCount = 0;
        
        foreach ($records as $record) {
            Event::dispatch(new TableActionExecuted('bulk_delete', 'users', $record));
            $record->delete();
            $deletedCount++;
        }
        
        // Log bulk action
        activity()
            ->withProperties(['count' => $deletedCount])
            ->causedBy(auth()->user())
            ->log('Bulk delete performed');
        
        return back()->with('success', "{$deletedCount} users deleted");
    }
}
```

## Complex Search Implementations

### Multi-Field Search with Weighting

```php
protected function applySearch(Builder $query): Builder
{
    $search = $this->getSearchQuery();
    
    if (!$search || !$this->searchable) {
        return $query;
    }
    
    return $query->selectRaw('*, 
        (CASE 
            WHEN name LIKE ? THEN 100
            WHEN email LIKE ? THEN 80
            WHEN phone LIKE ? THEN 60
            WHEN company LIKE ? THEN 40
            ELSE 20
        END) as relevance_score', [
            "%{$search}%",
            "%{$search}%", 
            "%{$search}%",
            "%{$search}%"
        ])
        ->where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
        })
        ->orderBy('relevance_score', 'desc');
}
```

### Elasticsearch Integration

```php
<?php

namespace App\Tables;

use Elasticsearch\ClientBuilder;

class ElasticsearchTable implements HasTable
{
    use InteractsWithTable;
    
    protected $elasticsearch;
    
    public function __construct()
    {
        $this->elasticsearch = ClientBuilder::create()
            ->setHosts(config('elasticsearch.hosts'))
            ->build();
    }
    
    protected function applySearch(Builder $query): Builder
    {
        $search = $this->getSearchQuery();
        
        if (!$search) {
            return $query;
        }
        
        // Search using Elasticsearch
        $results = $this->elasticsearch->search([
            'index' => 'users',
            'body' => [
                'query' => [
                    'multi_match' => [
                        'query' => $search,
                        'fields' => ['name^3', 'email^2', 'bio'],
                        'fuzziness' => 'AUTO',
                    ]
                ],
                'size' => 1000,
            ]
        ]);
        
        $userIds = collect($results['hits']['hits'])
            ->pluck('_source.id')
            ->toArray();
        
        if (empty($userIds)) {
            return $query->whereRaw('1 = 0'); // No results
        }
        
        return $query->whereIn('id', $userIds)
            ->orderByRaw('FIELD(id, ' . implode(',', $userIds) . ')');
    }
}
```

## Custom Table Builders

### Advanced Table Builder

```php
<?php

namespace App\Tables\Builders;

use Egmond\InertiaTables\Builder\TableBuilder;
use Illuminate\Database\Eloquent\Builder;

class AdvancedTableBuilder extends TableBuilder
{
    protected array $customFilters = [];
    protected array $aggregations = [];
    protected bool $enableCache = false;
    
    public function addFilter(string $name, \Closure $callback): static
    {
        $this->customFilters[$name] = $callback;
        return $this;
    }
    
    public function addAggregation(string $name, string $field, string $function = 'count'): static
    {
        $this->aggregations[$name] = compact('field', 'function');
        return $this;
    }
    
    public function enableCache(int $minutes = 60): static
    {
        $this->enableCache = $minutes;
        return $this;
    }
    
    public function build(Builder $query): TableResult
    {
        // Apply custom filters
        foreach ($this->customFilters as $name => $callback) {
            if (request()->has($name)) {
                $query = $callback($query, request($name));
            }
        }
        
        // Cache the query if enabled
        if ($this->enableCache) {
            $cacheKey = $this->generateCacheKey($query);
            
            return cache()->remember($cacheKey, $this->enableCache * 60, function () use ($query) {
                return parent::build($query);
            });
        }
        
        return parent::build($query);
    }
    
    protected function generateCacheKey(Builder $query): string
    {
        $params = [
            'query' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'request' => request()->all(),
            'user' => auth()->id(),
        ];
        
        return 'table_' . md5(serialize($params));
    }
}
```

## Testing Advanced Features

### Performance Testing

```php
<?php

namespace Tests\Performance;

use App\Models\User;
use App\Tables\UserTable;
use Tests\TestCase;

class TablePerformanceTest extends TestCase
{
    public function test_large_dataset_performance()
    {
        // Create large dataset
        User::factory()->count(10000)->create();
        
        $startTime = microtime(true);
        
        $table = UserTable::make();
        $result = $table->build(User::query());
        
        $executionTime = microtime(true) - $startTime;
        
        // Assert performance requirements
        $this->assertLessThan(1.0, $executionTime, 'Table build should complete in under 1 second');
        $this->assertLessThan(50, count($result->data), 'Should limit results to prevent memory issues');
    }
    
    public function test_search_performance_with_indexes()
    {
        User::factory()->count(5000)->create();
        
        $startTime = microtime(true);
        
        $request = request();
        $request->merge(['users' => ['search' => 'john']]);
        
        $table = new UserTable($request);
        $result = $table->build(User::query());
        
        $executionTime = microtime(true) - $startTime;
        
        $this->assertLessThan(0.5, $executionTime, 'Search should complete in under 500ms');
    }
}
```

### Integration Testing

```php
<?php

namespace Tests\Integration;

class AdvancedTableIntegrationTest extends TestCase
{
    public function test_multi_tenant_isolation()
    {
        $tenant1User = User::factory()->create(['tenant_id' => 'tenant1']);
        $tenant2User = User::factory()->create(['tenant_id' => 'tenant2']);
        
        $this->actingAs($tenant1User);
        
        $table = TenantAwareUserTable::forTenant('tenant1');
        $result = $table->build(User::query());
        
        // Should only see users from tenant1
        $this->assertCount(1, $result->data);
        $this->assertEquals('tenant1', $result->data[0]['tenant_id']);
    }
    
    public function test_dynamic_column_configuration()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Set custom column preferences
        $user->update([
            'table_preferences->users->visible_columns' => ['name', 'email']
        ]);
        
        $table = new ConfigurableUserTable();
        $result = $table->build(User::query());
        
        // Should only show configured columns
        $visibleColumns = collect($result->config['columns'])->pluck('key');
        $this->assertEquals(['name', 'email'], $visibleColumns->toArray());
    }
}
```

## Deployment Considerations

### Production Optimizations

```php
// config/inertia-tables.php
return [
    'cache' => [
        'enabled' => env('INERTIA_TABLES_CACHE', true),
        'ttl' => env('INERTIA_TABLES_CACHE_TTL', 300), // 5 minutes
        'tags' => ['inertia-tables'],
    ],
    
    'pagination' => [
        'max_per_page' => env('INERTIA_TABLES_MAX_PER_PAGE', 100),
        'default_per_page' => env('INERTIA_TABLES_DEFAULT_PER_PAGE', 15),
    ],
    
    'search' => [
        'min_length' => env('INERTIA_TABLES_SEARCH_MIN_LENGTH', 2),
        'debounce_ms' => env('INERTIA_TABLES_SEARCH_DEBOUNCE', 300),
    ],
];
```

### Monitoring and Logging

```php
<?php

namespace App\Tables\Concerns;

trait MonitorsPerformance
{
    protected function buildWithMonitoring(Builder $query): TableResult
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        
        $result = parent::build($query);
        
        $executionTime = microtime(true) - $startTime;
        $memoryUsage = memory_get_usage() - $startMemory;
        
        // Log performance metrics
        Log::info('Table performance', [
            'table' => $this->name,
            'execution_time' => $executionTime,
            'memory_usage' => $memoryUsage,
            'query_count' => count(\DB::getQueryLog()),
            'result_count' => count($result->data),
        ]);
        
        // Alert on poor performance
        if ($executionTime > 2.0) {
            Log::warning('Slow table query detected', [
                'table' => $this->name,
                'execution_time' => $executionTime,
            ]);
        }
        
        return $result;
    }
}
```

## Best Practices Summary

### 1. Database Optimization
- Always add indexes for sortable columns
- Use eager loading for relationships
- Implement full-text search for large datasets
- Consider cursor pagination for very large tables

### 2. Memory Management
- Use chunked processing for large operations
- Implement proper caching strategies
- Limit query results appropriately
- Monitor memory usage in production

### 3. Security
- Always validate and sanitize user input
- Implement proper authorization for all actions
- Use tenant isolation for multi-tenant applications
- Log sensitive operations for audit trails

### 4. Performance Monitoring
- Implement query logging and monitoring
- Set up alerts for slow queries
- Use performance testing in CI/CD
- Monitor memory usage and execution times

### 5. Scalability
- Design for horizontal scaling
- Use caching strategically
- Implement proper queue handling for long operations
- Consider read replicas for large read-heavy tables

## Next Steps

- **[API Reference](/09-api-reference)** - Complete API documentation
- **[Examples](/10-examples)** - Real-world implementation examples