# @tygoegmond/inertia-tables-react

React components for Inertia Tables with shadcn/ui integration.

## Installation

```bash
npm install @tygoegmond/inertia-tables-react
```

## Usage

### Basic Usage

```tsx
import { InertiaTable } from '@tygoegmond/inertia-tables-react';

function MyComponent() {
  // Data from your PHP backend (TableResult)
  const tableResult = {
    config: {
      columns: [
        {
          key: 'name',
          label: 'Name',
          type: 'text',
          visible: true,
          sortable: true,
          searchable: true
        },
        {
          key: 'status',
          label: 'Status',
          type: 'badge',
          visible: true,
          sortable: true,
          searchable: false,
          variant: 'success'
        }
      ],
      searchable: true,
      perPage: 25,
      defaultSort: {}
    },
    data: [
      { name: 'John Doe', status: 'Active' },
      { name: 'Jane Smith', status: 'Inactive' }
    ],
    pagination: {
      current_page: 1,
      per_page: 25,
      total: 2,
      last_page: 1,
      from: 1,
      to: 2
    },
    filters: {},
    sort: {},
    search: null
  };

  return (
    <InertiaTable 
      state={tableResult}
      onSearch={(query) => console.log('Search:', query)}
      onSort={(column, direction) => console.log('Sort:', column, direction)}
      onPageChange={(page) => console.log('Page:', page)}
    />
  );
}
```

### Individual Components

You can also use individual components:

```tsx
import { DataTable, TableSearch, TablePagination } from '@tygoegmond/inertia-tables-react';

function CustomTable() {
  return (
    <div className="space-y-4">
      <TableSearch 
        value={searchValue}
        onChange={setSearchValue}
        placeholder="Search records..."
      />
      
      <DataTable 
        state={tableResult}
        onSort={handleSort}
      />
      
      <TablePagination 
        pagination={tableResult.pagination}
        onPageChange={handlePageChange}
      />
    </div>
  );
}
```

## Column Types

### TextColumn
```php
// PHP
TextColumn::make('name')
    ->prefix('Mr. ')
    ->suffix(' Jr.')
    ->limit(50)
    ->copyable()
```

### BadgeColumn
```php
// PHP
BadgeColumn::make('status')
    ->variant('success') // default, primary, secondary, destructive, outline, success, warning, info
```

### IconColumn
```php
// PHP
IconColumn::make('type')
    ->icon('check')
    ->size(16)
```

### ImageColumn
```php
// PHP
ImageColumn::make('avatar')
    ->size(32)
    ->rounded()
```

### ActionColumn
```php
// PHP
ActionColumn::make('actions')
    ->actions([
        ['label' => 'Edit', 'icon' => 'edit'],
        ['label' => 'Delete', 'icon' => 'trash']
    ])
```

## Props

### InertiaTable Props

| Prop | Type | Description |
|------|------|-------------|
| `state` | `TableResult` | The table data from PHP backend |
| `onSearch` | `(query: string) => void` | Search handler |
| `onSort` | `(column: string, direction: 'asc' \| 'desc') => void` | Sort handler |
| `onPageChange` | `(page: number) => void` | Page change handler |
| `className` | `string` | CSS classes |

### TableResult Structure

```typescript
interface TableResult {
  config: {
    columns: TableColumn[];
    searchable: boolean;
    perPage: number;
    defaultSort: Record<string, 'asc' | 'desc'>;
  };
  data: any[];
  pagination: {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
    from: number | null;
    to: number | null;
  };
  filters: Record<string, any>;
  sort: Record<string, 'asc' | 'desc'>;
  search: string | null;
}
```

## Integration with Laravel/PHP

This package is designed to work seamlessly with the PHP package. Here's how to integrate:

```php
// In your Laravel controller
use Egmond\InertiaTables\Builder\TableBuilder;
use Egmond\InertiaTables\Columns\TextColumn;
use Egmond\InertiaTables\Columns\BadgeColumn;

$table = TableBuilder::make()
    ->columns([
        TextColumn::make('name')->sortable()->searchable(),
        BadgeColumn::make('status')->variant('success')
    ])
    ->searchable()
    ->paginate(25)
    ->build(User::query());

return inertia('Users', [
    'table' => $table
]);
```

```tsx
// In your React component
import { InertiaTable } from '@tygoegmond/inertia-tables-react';

export default function Users({ table }) {
  return (
    <InertiaTable 
      state={table}
      onSearch={(query) => router.get('/users', { search: query })}
      onSort={(column, direction) => router.get('/users', { sort: column, direction })}
      onPageChange={(page) => router.get('/users', { page })}
    />
  );
}
```

## License

MIT