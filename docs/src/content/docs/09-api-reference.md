---
title: API Reference
description: Complete API reference for all Inertia Tables classes, methods, and interfaces.
---

## Overview

This comprehensive API reference covers all public classes, methods, and interfaces provided by Inertia Tables. Use this as a quick reference for method signatures, parameters, and return types.

## Core Classes

### Table

The main table configuration class.

```php
class Table
```

#### Methods

| Method | Parameters | Return Type | Description |
|--------|------------|-------------|-------------|
| `as(string $name)` | `$name`: Table identifier | `static` | Set the table name for frontend state management |
| `query(Builder $query)` | `$query`: Eloquent query builder | `static` | Set the base query for the table |
| `columns(array $columns)` | `$columns`: Array of column instances | `static` | Define table columns |
| `actions(array $actions)` | `$actions`: Array of action instances | `static` | Define row actions |
| `bulkActions(array $bulkActions)` | `$bulkActions`: Array of bulk action instances | `static` | Define bulk actions |
| `headerActions(array $headerActions)` | `$headerActions`: Array of header action instances | `static` | Define header actions |
| `searchable(bool $searchable = true)` | `$searchable`: Enable/disable search | `static` | Enable global search functionality |
| `paginate(int $perPage)` | `$perPage`: Items per page | `static` | Enable pagination with specified page size |
| `defaultSort(string $column, string $direction = 'asc')` | `$column`: Column name<br>`$direction`: 'asc' or 'desc' | `static` | Set default sorting |
| `build()` | None | `TableResult` | Build and return the table result |

#### Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$columns` | `array` | `[]` | Table columns |
| `$query` | `?Builder` | `null` | Base Eloquent query |
| `$perPage` | `int` | `25` | Items per page |
| `$searchable` | `bool` | `false` | Whether table is searchable |
| `$name` | `?string` | `null` | Table identifier |

### TableResult

The result object returned by table builds.

```php
class TableResult
```

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$config` | `array` | Table configuration |
| `$data` | `array` | Table row data |
| `$pagination` | `array` | Pagination information |
| `$sort` | `array` | Current sort state |
| `$search` | `?string` | Current search query |
| `$name` | `string` | Table name |
| `$actions` | `array` | Row actions |
| `$bulkActions` | `array` | Bulk actions |
| `$headerActions` | `array` | Header actions |

## Column Classes

### BaseColumn

Abstract base class for all columns.

```php
abstract class BaseColumn
```

#### Methods

| Method | Parameters | Return Type | Description |
|--------|------------|-------------|-------------|
| `make(string $key)` | `$key`: Column database key | `static` | Create new column instance |
| `label(string $label)` | `$label`: Display label | `static` | Set column label |
| `visible(bool\|Closure $visible = true)` | `$visible`: Visibility condition | `static` | Control column visibility |
| `formatStateUsing(Closure $callback)` | `$callback`: Formatting function | `static` | Custom value formatting |
| `getKey()` | None | `string` | Get column database key |
| `getLabel()` | None | `string` | Get display label |
| `isVisible()` | None | `bool` | Check if column is visible |

### TextColumn

Column for displaying text data.

```php
class TextColumn extends BaseColumn
```

#### Methods

| Method | Parameters | Return Type | Description |
|--------|------------|-------------|-------------|
| `searchable(bool $searchable = true, ?string $column = null)` | `$searchable`: Enable search<br>`$column`: Custom search column | `static` | Make column searchable |
| `sortable(bool $sortable = true)` | `$sortable`: Enable sorting | `static` | Make column sortable |
| `prefix(string $prefix)` | `$prefix`: Text prefix | `static` | Add prefix to values |
| `suffix(string $suffix)` | `$suffix`: Text suffix | `static` | Add suffix to values |
| `limit(int $limit)` | `$limit`: Character limit | `static` | Limit displayed characters |
| `wrap(string $wrap)` | `$wrap`: 'truncate' or 'break-words' | `static` | Set text wrapping behavior |
| `copyable(bool $copyable = true)` | `$copyable`: Enable copy functionality | `static` | Enable copy to clipboard |
| `badge(bool $badge = true)` | `$badge`: Display as badge | `static` | Render as styled badge |
| `badgeVariant(string\|Closure $variant)` | `$variant`: Badge color variant | `static` | Set badge color |
| `defaultSort(string $direction = 'asc')` | `$direction`: Sort direction | `static` | Set default sort direction |

#### Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$prefix` | `?string` | `null` | Text prefix |
| `$suffix` | `?string` | `null` | Text suffix |
| `$limit` | `?int` | `null` | Character limit |
| `$wrap` | `string` | `'truncate'` | Text wrapping mode |
| `$copyable` | `bool` | `false` | Enable copy functionality |
| `$badge` | `bool` | `false` | Render as badge |

## Action Classes

### AbstractAction

Base class for all actions.

```php
abstract class AbstractAction
```

#### Methods

| Method | Parameters | Return Type | Description |
|--------|------------|-------------|-------------|
| `make(string $name)` | `$name`: Action identifier | `static` | Create new action instance |
| `label(string $label)` | `$label`: Display label | `static` | Set action label |
| `color(string $color)` | `$color`: Color variant | `static` | Set action color |
| `authorize(Closure $callback)` | `$callback`: Authorization function | `static` | Set authorization logic |
| `visible(bool\|Closure $visible = true)` | `$visible`: Visibility condition | `static` | Control action visibility |
| `disabled(bool\|Closure $disabled = false)` | `$disabled`: Disabled condition | `static` | Control action disabled state |
| `requiresConfirmation(string $title = '', string $message = '')` | `$title`: Dialog title<br>`$message`: Dialog message | `static` | Add confirmation dialog |
| `getName()` | None | `string` | Get action name |

#### Color Shortcuts

| Method | Equivalent | Description |
|--------|------------|-------------|
| `primary()` | `color('primary')` | Blue primary color |
| `success()` | `color('success')` | Green success color |
| `danger()` | `color('danger')` | Red danger color |
| `warning()` | `color('warning')` | Yellow warning color |
| `info()` | `color('info')` | Light blue info color |
| `gray()` | `color('gray')` | Gray secondary color |

### Action

Standard action for row and header operations.

```php
class Action extends AbstractAction
```

Inherits all methods from `AbstractAction`.

### BulkAction

Action for operating on multiple selected rows.

```php
class BulkAction extends AbstractAction
```

#### Additional Requirements

- **MUST** define `authorize()` method for security
- Handler methods receive array of records: `{actionName}BulkAction($records)`

## Traits

### InteractsWithTable

Trait for table classes to handle action execution.

```php
trait InteractsWithTable
```

#### Action Handler Methods

| Pattern | Parameters | Description |
|---------|------------|-------------|
| `{actionName}Action($record)` | `$record`: Eloquent model | Row action handler |
| `{actionName}BulkAction($records)` | `$records`: Array of models | Bulk action handler |
| `{actionName}Action()` | None | Header action handler |

### CanBeSearched

Trait for searchable functionality.

```php
trait CanBeSearched
```

#### Methods

| Method | Parameters | Return Type | Description |
|--------|------------|-------------|-------------|
| `searchable(bool $searchable = true, ?string $column = null)` | `$searchable`: Enable search<br>`$column`: Custom search column | `static` | Make searchable |
| `isSearchable()` | None | `bool` | Check if searchable |
| `getSearchColumn()` | None | `?string` | Get search column |

### CanBeSorted

Trait for sortable functionality.

```php
trait CanBeSorted
```

#### Methods

| Method | Parameters | Return Type | Description |
|--------|------------|-------------|-------------|
| `sortable(bool $sortable = true)` | `$sortable`: Enable sorting | `static` | Make sortable |
| `defaultSort(string $direction = 'asc')` | `$direction`: Sort direction | `static` | Set default sort |
| `isSortable()` | None | `bool` | Check if sortable |
| `getDefaultSortDirection()` | None | `?string` | Get default sort direction |

## Contracts/Interfaces

### HasTable

Interface for table classes.

```php
interface HasTable extends Arrayable, JsonSerializable
```

#### Required Methods

| Method | Parameters | Return Type | Description |
|--------|------------|-------------|-------------|
| `table(Table $table)` | `$table`: Table instance | `Table` | Configure the table |
| `getTable()` | None | `Table` | Get configured table |

### ActionContract

Base interface for all actions.

```php
interface ActionContract
```

#### Required Methods

| Method | Return Type | Description |
|--------|-------------|-------------|
| `getName()` | `string` | Get action name |

## React Components

### InertiaTable

Main React component for rendering tables.

```tsx
interface TableProps<T = Record<string, unknown>> {
  state: TableResult<T>;
  className?: string;
  customColumnRenderers?: Record<string, React.ComponentType<any>>;
  customActionRenderers?: Record<string, React.ComponentType<any>>;
  searchDebounce?: number;
  onSuccess?: (message: string) => void;
  onError?: (error: string) => void;
}
```

### Hooks

#### useInertiaTable

```tsx
interface UseInertiaTableProps {
  initialSearch?: string;
  preserveState?: boolean;
  preserveScroll?: boolean;
  tableState?: TableResult;
}

interface InertiaTableState {
  searchValue: string;
  setSearchValue: React.Dispatch<React.SetStateAction<string>>;
  handleSearch: (query: string) => void;
  handleSort: (column: string | null, direction: 'asc' | 'desc' | null) => void;
  handlePageChange: (page: number) => void;
  isNavigating: boolean;
}
```

#### useTableActions

```tsx
interface UseTableActionsProps {
  tableName: string;
  primaryKey: string;
  onSuccess?: (message: string) => void;
  onError?: (error: string) => void;
}

interface TableActionsState {
  executeAction: (actionName: string, record: any, data?: any) => void;
  executeBulkAction: (actionName: string, records: any[], data?: any) => void;
  executeHeaderAction: (actionName: string, data?: any) => void;
  isLoading: boolean;
  confirmationDialog: ConfirmationDialog | null;
  confirmAction: () => void;
  cancelAction: () => void;
}
```

## Type Definitions

### TypeScript Interfaces

```tsx
interface TableColumn {
  key: string;
  label: string;
  type: string;
  visible: boolean;
  sortable: boolean;
  searchable: boolean;
  searchColumn: string | null;
  defaultSort: 'asc' | 'desc' | null;
  state: Record<string, unknown>;
}

interface TableConfig {
  columns: TableColumn[];
  searchable: boolean;
  selectable?: boolean;
  perPage: number;
  defaultSort: Record<string, 'asc' | 'desc'>;
}

interface TablePagination {
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
  from: number | null;
  to: number | null;
  links: PaginationLink[];
}

interface TableAction {
  name: string;
  label: string;
  color: string;
  requiresConfirmation?: boolean;
  confirmationTitle?: string;
  confirmationMessage?: string;
}

interface TableResult<T = any> {
  config: TableConfig;
  data: T[];
  pagination: TablePagination;
  sort: Record<string, 'asc' | 'desc'>;
  search: string | null;
  name: string;
  actions: TableAction[];
  bulkActions: TableAction[];
  headerActions: TableAction[];
}
```

## Configuration

### Package Configuration

```php
// config/inertia-tables.php
return [
    'default_per_page' => 15,
    'max_per_page' => 100,
    'search_debounce' => 300,
    'cache_enabled' => true,
    'cache_ttl' => 300,
];
```

## URL Parameters

### Request Structure

Tables use nested parameters in the request:

```php
// Search: ?users[search]=john
// Sort: ?users[sort][name]=asc
// Page: ?page=2
// Combined: ?users[search]=john&users[sort][name]=asc&page=2
```

### Parameter Access

```php
// In your table class
protected function getSearchQuery(): ?string
{
    $tableParams = $this->request->get($this->name, []);
    return $tableParams['search'] ?? null;
}

protected function getSortData(): array
{
    $tableParams = $this->request->get($this->name, []);
    $sort = $tableParams['sort'] ?? [];
    return is_array($sort) ? $sort : [];
}
```

## Error Handling

### Exception Classes

| Exception | Thrown When | Handling |
|-----------|-------------|----------|
| `InvalidColumnException` | Invalid column configuration | Fix column definition |
| `SerializationException` | Data serialization fails | Check data types |
| `AuthorizationException` | Action not authorized | Update authorization logic |

### Error Responses

```php
// Action error handling
try {
    $result = $this->executeAction($record);
    return $result;
} catch (\Exception $e) {
    return back()->with('error', 'Action failed: ' . $e->getMessage());
}
```

## Performance Considerations

### Query Optimization

- Always eager load relationships used in columns
- Add database indexes for sortable columns
- Use `select()` to limit retrieved columns
- Consider query caching for expensive operations

### Memory Management

- Use `chunk()` for large dataset operations
- Implement proper pagination limits
- Monitor memory usage in production
- Use cursor pagination for very large tables

## Version Compatibility

### PHP Requirements

- **PHP**: 8.2+
- **Laravel**: 11.0+ (also supports 10.0+)

### JavaScript Requirements

- **React**: 18.2+ (also supports 19.0+)
- **Inertia.js**: 1.3+ (also supports 2.0+)
- **TypeScript**: 5.0+ (optional but recommended)

## Migration Guide

### From Version 0.1.x to 0.2.x

```php
// Old syntax
TextColumn::make('name')->searchable(true)

// New syntax (no changes needed)
TextColumn::make('name')->searchable()
```

No breaking changes in current version.

## Common Patterns

### Custom Table Builder

```php
<?php

namespace App\Tables;

use Egmond\InertiaTables\Builder\TableBuilder;

class CustomTableBuilder extends TableBuilder
{
    public function build(Builder $query): TableResult
    {
        // Custom logic before building
        $query = $this->applyCustomFilters($query);
        
        $result = parent::build($query);
        
        // Custom logic after building
        $result->customData = $this->addCustomData();
        
        return $result;
    }
}
```

### Reusable Action

```php
<?php

namespace App\Tables\Actions;

class ToggleStatusAction extends Action
{
    public function __construct(string $name = 'toggle_status')
    {
        parent::__construct($name);
        
        $this->label('Toggle Status')
             ->color('warning')
             ->authorize(fn($record) => auth()->user()->can('update', $record));
    }
}
```

## Best Practices

### Security
- Always authorize actions with `authorize()`
- Validate all input data
- Use CSRF protection (automatic with Inertia)
- Implement proper error handling

### Performance
- Add database indexes for sortable/searchable columns
- Use eager loading for relationships
- Implement caching for expensive queries
- Monitor query performance in production

### Code Organization
- Create reusable column and action classes
- Use traits for common functionality
- Organize table classes in dedicated namespace
- Write tests for complex table logic

## Further Reading

- **[Getting Started](/02-getting-started)** - Basic usage tutorial
- **[Columns](/03-columns/01-getting-started)** - Column system overview
- **[Actions](/04-actions/01-getting-started)** - Action system overview
- **[Examples](/10-examples)** - Real-world implementation examples