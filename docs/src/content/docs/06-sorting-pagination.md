---
title: Sorting & Pagination
description: Learn how to implement sorting and pagination in your Inertia Tables for better data navigation and organization.
---

## Overview

Inertia Tables provides built-in sorting and pagination functionality that allows users to organize and navigate through large datasets efficiently. The system supports column-based sorting with relationship support and customizable pagination options.

## Sorting

### Enable Column Sorting

Make individual columns sortable by calling the `sortable()` method:

```php
->columns([
    TextColumn::make('name')
        ->sortable(), // Users can sort by name
        
    TextColumn::make('email')
        ->sortable(), // Users can sort by email
        
    TextColumn::make('created_at')
        ->sortable(), // Users can sort by creation date
])
```

### Default Sorting

Set default sorting for your table:

```php
// Method 1: Set default sort on the table
public function table(Table $table): Table
{
    return $table->as('users')
        ->query(User::query())
        ->columns([
            TextColumn::make('name')->sortable(),
            TextColumn::make('created_at')->sortable(),
        ])
        ->defaultSort('created_at', 'desc') // Sort by created_at descending by default
        ->paginate(15);
}

// Method 2: Set default sort on individual columns
TextColumn::make('created_at')
    ->sortable()
    ->defaultSort('desc') // This column defaults to descending sort
```

### Multiple Column Sorting

Set up multiple default sort columns:

```php
public function table(Table $table): Table
{
    return $table
        ->defaultSort('status', 'asc')     // Primary sort
        ->defaultSort('created_at', 'desc') // Secondary sort
        ->columns([
            TextColumn::make('status')->sortable(),
            TextColumn::make('name')->sortable(),
            TextColumn::make('created_at')->sortable(),
        ]);
}
```

## Relationship Sorting

### Sort by Relationship Columns

Sort by relationship data using dot notation:

```php
->columns([
    TextColumn::make('name')->sortable(),
    TextColumn::make('author.name')
        ->label('Author')
        ->sortable(), // Sort by the author's name
    TextColumn::make('category.title')
        ->label('Category') 
        ->sortable(), // Sort by category title
])
```

**Important**: Make sure to eager load relationships to avoid N+1 queries:

```php
public function table(Table $table): Table
{
    return $table
        ->query(
            Post::query()
                ->with(['author', 'category']) // Eager load relationships
        )
        ->columns([
            TextColumn::make('title')->sortable(),
            TextColumn::make('author.name')->sortable(),
            TextColumn::make('category.title')->sortable(),
        ]);
}
```

### Complex Relationship Sorting

For more complex relationship sorting, you can customize the query:

```php
public function table(Table $table): Table
{
    return $table
        ->query(
            User::query()
                ->leftJoin('profiles', 'users.id', '=', 'profiles.user_id')
                ->select('users.*', 'profiles.company as profile_company')
        )
        ->columns([
            TextColumn::make('name')->sortable(),
            TextColumn::make('profile_company')
                ->label('Company')
                ->sortable(),
        ]);
}
```

## Pagination

### Basic Pagination

Enable pagination by calling the `paginate()` method:

```php
public function table(Table $table): Table
{
    return $table->as('users')
        ->query(User::query())
        ->columns([
            TextColumn::make('name'),
            TextColumn::make('email'),
        ])
        ->paginate(15); // Show 15 records per page
}
```

### Custom Page Sizes

Allow users to choose page sizes:

```php
public function table(Table $table): Table
{
    $perPage = request('per_page', 15); // Default to 15, allow override
    
    return $table
        ->paginate($perPage)
        ->columns([
            TextColumn::make('name'),
            TextColumn::make('email'),
        ]);
}
```

Create a page size selector in your frontend:

```tsx
import { InertiaTable } from '@tygoegmond/inertia-tables-react';
import { router } from '@inertiajs/react';

export default function UsersIndex({ users }) {
  const handlePageSizeChange = (newPageSize: number) => {
    router.get(route('users.index'), { per_page: newPageSize }, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  return (
    <div>
      <div className="mb-4">
        <label className="text-sm font-medium text-gray-700">Show:</label>
        <select 
          value={users.pagination.per_page}
          onChange={(e) => handlePageSizeChange(Number(e.target.value))}
          className="ml-2 rounded border-gray-300"
        >
          <option value={10}>10</option>
          <option value={25}>25</option>
          <option value={50}>50</option>
          <option value={100}>100</option>
        </select>
        <span className="ml-2 text-sm text-gray-500">per page</span>
      </div>
      
      <InertiaTable state={users} />
    </div>
  );
}
```

## Advanced Sorting

### Custom Sort Logic

Override the default sorting behavior for complex requirements:

```php
<?php

namespace App\Tables;

use Egmond\InertiaTables\Builder\TableBuilder;
use Illuminate\Database\Eloquent\Builder;

class CustomSortTable extends TableBuilder
{
    protected function applySorting(Builder $query): Builder
    {
        $sortData = $this->getSortData();
        
        if (empty($sortData)) {
            $sortData = $this->defaultSort;
        }
        
        foreach ($sortData as $column => $direction) {
            switch ($column) {
                case 'priority':
                    // Custom priority sorting (high, medium, low)
                    $query->orderByRaw("
                        CASE priority 
                            WHEN 'high' THEN 1 
                            WHEN 'medium' THEN 2 
                            WHEN 'low' THEN 3 
                            ELSE 4 
                        END {$direction}
                    ");
                    break;
                    
                case 'status':
                    // Custom status sorting
                    $query->orderByRaw("
                        CASE status 
                            WHEN 'active' THEN 1 
                            WHEN 'pending' THEN 2 
                            WHEN 'inactive' THEN 3 
                        END {$direction}
                    ");
                    break;
                    
                default:
                    // Default sorting for other columns
                    if (isset($this->columns[$column]) && $this->columns[$column]->isSortable()) {
                        $query->orderBy($column, $direction);
                    }
            }
        }
        
        return $query;
    }
}
```

### Computed Column Sorting

Sort by computed or aggregated values:

```php
public function table(Table $table): Table
{
    return $table
        ->query(
            User::query()
                ->withCount('posts') // Add posts_count column
                ->selectRaw('users.*, CONCAT(first_name, " ", last_name) as full_name')
        )
        ->columns([
            TextColumn::make('full_name')
                ->label('Full Name')
                ->sortable(),
                
            TextColumn::make('posts_count')
                ->label('Total Posts')
                ->sortable(),
        ]);
}
```

### Null Values Handling

Control how null values are sorted:

```php
protected function applySorting(Builder $query): Builder
{
    $sortData = $this->getSortData();
    
    foreach ($sortData as $column => $direction) {
        if ($column === 'optional_field') {
            // Put nulls last regardless of sort direction
            $query->orderByRaw("ISNULL({$column}), {$column} {$direction}");
        } else {
            $query->orderBy($column, $direction);
        }
    }
    
    return $query;
}
```

## Performance Optimization

### Database Indexes

Ensure sortable columns have appropriate indexes:

```php
// In your migration
Schema::table('users', function (Blueprint $table) {
    $table->index('name');
    $table->index('created_at');
    $table->index('status');
    
    // Composite index for multi-column sorting
    $table->index(['status', 'created_at']);
});

// For relationship sorting
Schema::table('posts', function (Blueprint $table) {
    $table->index(['user_id', 'created_at']);
});
```

### Efficient Pagination

Use cursor-based pagination for large datasets:

```php
public function table(Table $table): Table
{
    $query = User::query()->orderBy('id');
    
    // Use cursor pagination for better performance on large datasets
    if (request('cursor')) {
        $query->where('id', '>', request('cursor'));
    }
    
    return $table
        ->query($query)
        ->paginate(100);
}
```

### Pagination with Counts

Disable counting for better performance on very large tables:

```php
public function table(Table $table): Table
{
    return $table
        ->query(User::query())
        ->simplePaginate(25); // Use simplePaginate() instead of paginate()
}
```

## Frontend Integration

### Custom Sorting Interface

Create custom sort controls:

```tsx
import { InertiaTable } from '@tygoegmond/inertia-tables-react';
import { router } from '@inertiajs/react';

export default function UsersIndex({ users }) {
  const currentSort = users.sort;
  
  const handleSort = (column: string) => {
    const currentDirection = currentSort[column];
    const newDirection = 
      currentDirection === 'asc' ? 'desc' : 
      currentDirection === 'desc' ? null : 'asc';
    
    const sortParams = newDirection ? { [column]: newDirection } : {};
    
    router.get(route('users.index'), {
      ...route().params,
      users: { sort: sortParams }
    }, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  return (
    <div>
      <div className="mb-4 flex space-x-4">
        <button
          onClick={() => handleSort('name')}
          className={`px-3 py-1 rounded ${
            currentSort.name ? 'bg-blue-100 text-blue-800' : 'bg-gray-100'
          }`}
        >
          Sort by Name {currentSort.name && (currentSort.name === 'asc' ? '↑' : '↓')}
        </button>
        
        <button
          onClick={() => handleSort('created_at')}
          className={`px-3 py-1 rounded ${
            currentSort.created_at ? 'bg-blue-100 text-blue-800' : 'bg-gray-100'
          }`}
        >
          Sort by Date {currentSort.created_at && (currentSort.created_at === 'asc' ? '↑' : '↓')}
        </button>
      </div>
      
      <InertiaTable state={users} />
    </div>
  );
}
```

### Pagination Info Display

Show detailed pagination information:

```tsx
export default function UsersIndex({ users }) {
  const { pagination } = users;
  
  return (
    <div>
      <div className="mb-4 text-sm text-gray-600">
        Showing {pagination.from} to {pagination.to} of {pagination.total} results
        (Page {pagination.current_page} of {pagination.last_page})
      </div>
      
      <InertiaTable state={users} />
      
      <div className="mt-4 flex justify-between items-center">
        <button
          disabled={!pagination.prev_page_url}
          onClick={() => router.get(pagination.prev_page_url)}
          className="px-4 py-2 bg-gray-300 rounded disabled:opacity-50"
        >
          Previous
        </button>
        
        <span className="text-sm text-gray-600">
          Page {pagination.current_page} of {pagination.last_page}
        </span>
        
        <button
          disabled={!pagination.next_page_url}
          onClick={() => router.get(pagination.next_page_url)}
          className="px-4 py-2 bg-gray-300 rounded disabled:opacity-50"
        >
          Next
        </button>
      </div>
    </div>
  );
}
```

## Testing Sorting and Pagination

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Tables\UserTable;
use Tests\TestCase;

class SortingPaginationTest extends TestCase
{
    public function test_table_sorts_by_name_ascending()
    {
        User::factory()->create(['name' => 'Charlie']);
        User::factory()->create(['name' => 'Alice']);
        User::factory()->create(['name' => 'Bob']);
        
        $request = request();
        $request->merge(['users' => ['sort' => ['name' => 'asc']]]);
        
        $table = new UserTable($request);
        $result = $table->build(User::query());
        
        $names = collect($result->data)->pluck('name')->toArray();
        $this->assertEquals(['Alice', 'Bob', 'Charlie'], $names);
    }
    
    public function test_table_sorts_by_name_descending()
    {
        User::factory()->create(['name' => 'Alice']);
        User::factory()->create(['name' => 'Charlie']);
        User::factory()->create(['name' => 'Bob']);
        
        $request = request();
        $request->merge(['users' => ['sort' => ['name' => 'desc']]]);
        
        $table = new UserTable($request);
        $result = $table->build(User::query());
        
        $names = collect($result->data)->pluck('name')->toArray();
        $this->assertEquals(['Charlie', 'Bob', 'Alice'], $names);
    }
    
    public function test_pagination_limits_results()
    {
        User::factory()->count(50)->create();
        
        $table = new UserTable();
        $result = $table->build(User::query());
        
        $this->assertCount(15, $result->data); // Default page size
        $this->assertEquals(50, $result->pagination['total']);
        $this->assertEquals(4, $result->pagination['last_page']); // 50/15 = 4 pages
    }
}
```

## Common Patterns

### Sort with Search

Maintain sorting when searching:

```php
public function table(Table $table): Table
{
    return $table
        ->defaultSort('relevance_score', 'desc') // Sort by relevance when searching
        ->defaultSort('created_at', 'desc')      // Default sort when not searching
        ->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('email')->searchable()->sortable(),
        ])
        ->searchable()
        ->paginate(20);
}

protected function applySorting(Builder $query): Builder
{
    $search = $this->getSearchQuery();
    $sortData = $this->getSortData();
    
    // If searching and no explicit sort, sort by relevance
    if ($search && empty($sortData)) {
        return $query->orderByRaw('
            CASE 
                WHEN name LIKE ? THEN 1
                WHEN email LIKE ? THEN 2
                ELSE 3
            END, name ASC
        ', ["{$search}%", "{$search}%"]);
    }
    
    return parent::applySorting($query);
}
```

### Remember User Preferences

Store user sorting and pagination preferences:

```php
public function table(Table $table): Table
{
    $user = auth()->user();
    $preferences = $user->table_preferences['users'] ?? [];
    
    $perPage = request('per_page', $preferences['per_page'] ?? 15);
    $sort = request('sort', $preferences['sort'] ?? ['created_at' => 'desc']);
    
    // Save preferences when they change
    if (request()->hasAny(['per_page', 'sort'])) {
        $user->update([
            'table_preferences->users' => [
                'per_page' => $perPage,
                'sort' => $sort,
            ],
        ]);
    }
    
    return $table
        ->defaultSort($sort)
        ->paginate($perPage);
}
```

## Best Practices

### 1. Always Add Database Indexes

```php
// Add indexes for frequently sorted columns
Schema::table('users', function (Blueprint $table) {
    $table->index('name');        // For name sorting
    $table->index('created_at');  // For date sorting
    $table->index('status');      // For status sorting
});
```

### 2. Use Sensible Default Sorting

```php
// Good: Logical default sort
->defaultSort('created_at', 'desc')  // Newest first
->defaultSort('name', 'asc')         // Alphabetical

// Avoid: No default sort on large tables
// Users expect some predictable order
```

### 3. Consider Performance for Large Datasets

```php
// For very large tables, use cursor pagination
public function index()
{
    $users = User::cursorPaginate(50);
    
    return Inertia::render('Users/Index', [
        'users' => UserTable::make()->buildCursor($users),
    ]);
}
```

### 4. Provide Sort Indicators

The built-in table component automatically shows sort indicators, but for custom interfaces, always show current sort state:

```tsx
// Show current sort direction with visual indicators
<button className="flex items-center">
  Name 
  {currentSort.name === 'asc' && <ChevronUpIcon />}
  {currentSort.name === 'desc' && <ChevronDownIcon />}
</button>
```

## Next Steps

- **[React Integration](/07-react-integration)** - Customize frontend sorting and pagination components
- **[Advanced Usage](/08-advanced-usage)** - Performance optimization and advanced patterns
- **[Examples](/10-examples)** - Real-world sorting and pagination examples

## Sorting & Pagination Reference

### Table Methods

| Method | Description | Example |
|--------|-------------|---------|
| `paginate(int $perPage)` | Enable pagination with page size | `->paginate(15)` |
| `simplePaginate(int $perPage)` | Simple pagination without counts | `->simplePaginate(25)` |
| `defaultSort(string $column, string $direction)` | Set default sorting | `->defaultSort('created_at', 'desc')` |

### Column Methods

| Method | Description | Example |
|--------|-------------|---------|
| `sortable(bool $sortable = true)` | Make column sortable | `->sortable()` |
| `defaultSort(string $direction)` | Set column default sort | `->defaultSort('desc')` |

### URL Parameter Structure

```php
// Sorting: /users?users[sort][name]=asc&users[sort][created_at]=desc
// Pagination: /users?page=2
// Combined: /users?users[sort][name]=asc&page=2&per_page=25
```

### Pagination Data Structure

```php
$users->pagination = [
    'current_page' => 1,
    'last_page' => 10,
    'per_page' => 15,
    'total' => 142,
    'from' => 1,
    'to' => 15,
    'prev_page_url' => null,
    'next_page_url' => '/users?page=2',
    'links' => [...] // Pagination link data
];
```