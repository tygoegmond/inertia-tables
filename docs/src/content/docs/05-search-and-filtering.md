---
title: Search & Filtering
description: Learn how to implement powerful search and filtering capabilities in your Inertia Tables.
---

## Overview

Inertia Tables provides built-in search functionality that allows users to quickly find specific records in your tables. The search system is flexible, allowing you to enable search on individual columns and customize how search queries are applied to your data.

## Basic Search Setup

### Enable Table Search

Enable search at the table level by calling the `searchable()` method:

```php
public function table(Table $table): Table
{
    return $table->as('users')
        ->query(User::query())
        ->columns([
            TextColumn::make('name'),
            TextColumn::make('email'),
        ])
        ->searchable() // Enables search functionality
        ->paginate(15);
}
```

This adds a search input to your table interface.

### Make Columns Searchable

Individual columns must be marked as searchable to be included in search queries:

```php
->columns([
    TextColumn::make('name')
        ->searchable(), // This column will be searched
        
    TextColumn::make('email')
        ->searchable(), // This column will be searched
        
    TextColumn::make('created_at'), // This column will NOT be searched
])
```

## Column-Specific Search

### Custom Search Columns

Search a different database column than the display column:

```php
TextColumn::make('display_name')
    ->searchable()
    ->searchColumn('full_name') // Search the 'full_name' column instead
```

### Multiple Column Search

Search across multiple columns for a single display column:

```php
TextColumn::make('full_name')
    ->searchable()
    ->searchColumn('first_name,last_name') // Search both first_name and last_name
```

### Relationship Search

Search relationship data using dot notation:

```php
TextColumn::make('author.name')
    ->label('Author')
    ->searchable() // Automatically searches the 'name' column on the 'author' relationship
```

Make sure to eager load the relationship:

```php
public function table(Table $table): Table
{
    return $table
        ->query(User::query()->with('author'))
        ->columns([
            TextColumn::make('title')->searchable(),
            TextColumn::make('author.name')->searchable(),
        ])
        ->searchable();
}
```

## Advanced Search Patterns

### Custom Search Logic

For complex search requirements, you can override the search behavior in your table builder:

```php
<?php

namespace App\Tables;

use Egmond\InertiaTables\Builder\TableBuilder;
use Illuminate\Database\Eloquent\Builder;

class CustomUserTable extends TableBuilder
{
    protected function applySearch(Builder $query): Builder
    {
        $search = $this->getSearchQuery();
        
        if (!$search || !$this->searchable) {
            return $query;
        }
        
        return $query->where(function ($query) use ($search) {
            // Search in user fields
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  
            // Search in profile fields
            ->orWhereHas('profile', function ($profileQuery) use ($search) {
                $profileQuery->where('company', 'like', "%{$search}%")
                           ->orWhere('bio', 'like', "%{$search}%");
            })
            
            // Search in related posts
            ->orWhereHas('posts', function ($postQuery) use ($search) {
                $postQuery->where('title', 'like', "%{$search}%");
            });
        });
    }
}
```

### Full-Text Search

Implement MySQL full-text search for better performance on large datasets:

```php
protected function applySearch(Builder $query): Builder
{
    $search = $this->getSearchQuery();
    
    if (!$search || !$this->searchable) {
        return $query;
    }
    
    // Use MySQL full-text search
    return $query->whereRaw(
        "MATCH(name, email, bio) AGAINST(? IN BOOLEAN MODE)",
        [$search]
    );
}
```

### Searchable Scopes

Use Eloquent scopes for reusable search logic:

```php
// In your User model
public function scopeSearch($query, $term)
{
    return $query->where(function ($query) use ($term) {
        $query->where('name', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhereHas('profile', function ($profileQuery) use ($term) {
                  $profileQuery->where('company', 'like', "%{$term}%");
              });
    });
}

// In your table class
protected function applySearch(Builder $query): Builder
{
    $search = $this->getSearchQuery();
    
    if (!$search || !$this->searchable) {
        return $query;
    }
    
    return $query->search($search);
}
```

## Search with Filters

### Combining Search and Filters

You can combine search functionality with custom filters:

```php
public function table(Table $table): Table
{
    return $table->as('users')
        ->query($this->getFilteredQuery())
        ->columns([
            TextColumn::make('name')->searchable(),
            TextColumn::make('email')->searchable(),
            TextColumn::make('status'),
        ])
        ->searchable()
        ->paginate(15);
}

protected function getFilteredQuery()
{
    $query = User::query();
    
    // Apply status filter
    if (request('status')) {
        $query->where('status', request('status'));
    }
    
    // Apply date range filter
    if (request('date_from')) {
        $query->whereDate('created_at', '>=', request('date_from'));
    }
    
    if (request('date_to')) {
        $query->whereDate('created_at', '<=', request('date_to'));
    }
    
    return $query;
}
```

### Filter Interface

Create a filter interface alongside your table:

```tsx
import { InertiaTable } from '@tygoegmond/inertia-tables-react';
import { useState } from 'react';
import { router } from '@inertiajs/react';

interface Filters {
  status?: string;
  date_from?: string;
  date_to?: string;
}

export default function UsersIndex({ users, filters }: { users: any, filters: Filters }) {
  const [localFilters, setLocalFilters] = useState<Filters>(filters);

  const applyFilters = () => {
    router.get(route('users.index'), localFilters, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  const clearFilters = () => {
    router.get(route('users.index'), {}, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  return (
    <div className="space-y-6">
      {/* Filter Panel */}
      <div className="bg-white p-4 rounded-lg shadow">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700">Status</label>
            <select
              value={localFilters.status || ''}
              onChange={(e) => setLocalFilters({...localFilters, status: e.target.value})}
              className="mt-1 block w-full rounded-md border-gray-300"
            >
              <option value="">All Statuses</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="pending">Pending</option>
            </select>
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700">From Date</label>
            <input
              type="date"
              value={localFilters.date_from || ''}
              onChange={(e) => setLocalFilters({...localFilters, date_from: e.target.value})}
              className="mt-1 block w-full rounded-md border-gray-300"
            />
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700">To Date</label>
            <input
              type="date"
              value={localFilters.date_to || ''}
              onChange={(e) => setLocalFilters({...localFilters, date_to: e.target.value})}
              className="mt-1 block w-full rounded-md border-gray-300"
            />
          </div>
          
          <div className="flex items-end space-x-2">
            <button
              onClick={applyFilters}
              className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
            >
              Apply Filters
            </button>
            <button
              onClick={clearFilters}
              className="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
            >
              Clear
            </button>
          </div>
        </div>
      </div>

      {/* Table */}
      <InertiaTable state={users} />
    </div>
  );
}
```

## Search Performance

### Database Indexes

Ensure your searchable columns have appropriate database indexes:

```php
// In your migration
Schema::table('users', function (Blueprint $table) {
    $table->index('name');
    $table->index('email');
    $table->index(['name', 'email']); // Composite index
});

// For full-text search
Schema::table('users', function (Blueprint $table) {
    $table->fullText(['name', 'email', 'bio']);
});
```

### Limiting Search Results

For large datasets, consider limiting search results:

```php
protected function applySearch(Builder $query): Builder
{
    $search = $this->getSearchQuery();
    
    if (!$search || !$this->searchable) {
        return $query;
    }
    
    // Limit search results to improve performance
    return $query->where(function ($query) use ($search) {
        // Your search logic
    })->limit(1000); // Limit to first 1000 matches
}
```

### Search Debouncing

The frontend automatically debounces search input to reduce server requests:

```tsx
// Built into InertiaTable component
// Search requests are debounced by 300ms by default
<InertiaTable 
  state={users}
  searchDebounce={500} // Customize debounce time
/>
```

## Search Analytics

### Track Search Queries

Log search queries for analytics:

```php
protected function applySearch(Builder $query): Builder
{
    $search = $this->getSearchQuery();
    
    if (!$search || !$this->searchable) {
        return $query;
    }
    
    // Log search query
    Log::info('Table search performed', [
        'table' => $this->name,
        'query' => $search,
        'user_id' => auth()->id(),
        'timestamp' => now(),
    ]);
    
    return $query->where(function ($query) use ($search) {
        // Your search logic
    });
}
```

### Search Suggestions

Implement search suggestions based on popular queries:

```php
public function getSearchSuggestions(Request $request)
{
    $partial = $request->get('q');
    
    $suggestions = collect([
        // Popular searches
        'Active users',
        'Recent signups',
        'Premium members',
    ])
    ->filter(fn($suggestion) => str_contains(strtolower($suggestion), strtolower($partial)))
    ->take(5);
    
    return response()->json($suggestions);
}
```

## Testing Search Functionality

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Tables\UserTable;
use Tests\TestCase;

class SearchTest extends TestCase
{
    public function test_search_finds_users_by_name()
    {
        $user1 = User::factory()->create(['name' => 'John Doe']);
        $user2 = User::factory()->create(['name' => 'Jane Smith']);
        
        // Simulate search request
        $request = request();
        $request->merge(['users' => ['search' => 'John']]);
        
        $table = new UserTable($request);
        $result = $table->build(User::query());
        
        $this->assertCount(1, $result->data);
        $this->assertEquals('John Doe', $result->data[0]['name']);
    }
    
    public function test_search_works_across_multiple_columns()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        
        // Search by name
        $request = request();
        $request->merge(['users' => ['search' => 'John']]);
        $table = new UserTable($request);
        $result = $table->build(User::query());
        $this->assertCount(1, $result->data);
        
        // Search by email
        $request = request();
        $request->merge(['users' => ['search' => 'john@example.com']]);
        $table = new UserTable($request);
        $result = $table->build(User::query());
        $this->assertCount(1, $result->data);
    }
}
```

## Common Search Patterns

### Case-Insensitive Search

```php
protected function applySearch(Builder $query): Builder
{
    $search = $this->getSearchQuery();
    
    if (!$search || !$this->searchable) {
        return $query;
    }
    
    return $query->where(function ($query) use ($search) {
        $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%'])
              ->orWhereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%']);
    });
}
```

### Exact Match Search

```php
protected function applySearch(Builder $query): Builder
{
    $search = $this->getSearchQuery();
    
    if (!$search || !$this->searchable) {
        return $query;
    }
    
    // Check if search term is enclosed in quotes for exact match
    if (preg_match('/^"(.+)"$/', $search, $matches)) {
        $exactTerm = $matches[1];
        return $query->where(function ($query) use ($exactTerm) {
            $query->where('name', '=', $exactTerm)
                  ->orWhere('email', '=', $exactTerm);
        });
    }
    
    // Default fuzzy search
    return $query->where(function ($query) use ($search) {
        $query->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
    });
}
```

### Search with Ranking

```php
protected function applySearch(Builder $query): Builder
{
    $search = $this->getSearchQuery();
    
    if (!$search || !$this->searchable) {
        return $query;
    }
    
    return $query->selectRaw('*, 
        CASE 
            WHEN name = ? THEN 100
            WHEN name LIKE ? THEN 80
            WHEN email = ? THEN 60
            WHEN email LIKE ? THEN 40
            ELSE 20
        END as search_score', [
            $search, // Exact name match
            $search . '%', // Name starts with
            $search, // Exact email match
            $search . '%', // Email starts with
        ])
        ->where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        })
        ->orderBy('search_score', 'desc');
}
```

## Best Practices

### 1. Choose Searchable Columns Wisely

```php
// Good: Search meaningful text fields
TextColumn::make('name')->searchable()
TextColumn::make('email')->searchable()
TextColumn::make('description')->searchable()

// Avoid: Searching dates, IDs, or large text fields without indexes
TextColumn::make('id') // Don't make searchable
TextColumn::make('created_at') // Usually not useful for search
```

### 2. Use Appropriate Search Methods

```php
// For small datasets: Simple LIKE queries
->where('name', 'like', "%{$search}%")

// For medium datasets: Indexed LIKE queries with proper indexes
->where('indexed_field', 'like', "%{$search}%")

// for large datasets: Full-text search
->whereRaw("MATCH(name, description) AGAINST(? IN BOOLEAN MODE)", [$search])
```

### 3. Provide Search Feedback

```tsx
export default function UsersIndex({ users }) {
  const searchQuery = users.search;
  const totalResults = users.pagination.total;

  return (
    <div>
      {searchQuery && (
        <div className="mb-4 text-sm text-gray-600">
          Showing {totalResults} results for "{searchQuery}"
          <button 
            onClick={() => router.get(route('users.index'))}
            className="ml-2 text-blue-600 hover:underline"
          >
            Clear search
          </button>
        </div>
      )}
      
      <InertiaTable state={users} />
    </div>
  );
}
```

## Next Steps

- **[Sorting & Pagination](/06-sorting-pagination)** - Learn about sorting and pagination features
- **[React Integration](/07-react-integration)** - Customize the frontend search interface
- **[Advanced Usage](/08-advanced-usage)** - Performance optimization and advanced patterns

## Search Reference

### Table Methods

| Method | Description | Example |
|--------|-------------|---------|
| `searchable(bool $searchable = true)` | Enable/disable table search | `->searchable()` |

### Column Methods

| Method | Description | Example |
|--------|-------------|---------|
| `searchable(bool $searchable = true, ?string $column = null)` | Make column searchable | `->searchable()` |
| `searchColumn(string $column)` | Specify search column | `->searchColumn('full_name')` |

### Search Query Structure

The search query is passed to your table via the request parameters:

```php
// URL: /users?users[search]=john
$searchQuery = request('users.search'); // "john"

// In your table builder
protected function getSearchQuery(): ?string
{
    $tableParams = $this->request->get($this->name, []);
    return $tableParams['search'] ?? null;
}
```