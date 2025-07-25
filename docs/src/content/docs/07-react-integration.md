---
title: React Integration
description: Learn how to use and customize the React components for Inertia Tables in your frontend application.
---

## Overview

Inertia Tables provides a complete React component library built with modern React patterns, TypeScript support, and shadcn/ui components. The frontend components automatically handle search, sorting, pagination, and actions with minimal configuration.

## Basic Usage

### InertiaTable Component

The main component for rendering tables:

```tsx
import { InertiaTable } from '@tygoegmond/inertia-tables-react';
import { TableResult } from '@tygoegmond/inertia-tables-react/types';

interface UsersPageProps {
  users: TableResult;
}

export default function UsersPage({ users }: UsersPageProps) {
  return (
    <div className="p-6">
      <h1 className="text-2xl font-bold mb-6">Users</h1>
      <InertiaTable 
        state={users}
        className="shadow-sm border border-gray-200 rounded-lg"
      />
    </div>
  );
}
```

### Component Props

The `InertiaTable` component accepts the following props:

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

## Deferred Loading

Handle deferred table data with built-in loading states:

```tsx
// In your controller
return Inertia::render('Users/Index', [
  'users' => Inertia::defer(fn () => UserTable::make()),
]);
```

```tsx
// In your React component - no changes needed!
export default function UsersIndex({ users }) {
  return (
    <div>
      {/* Component automatically shows loading state for deferred data */}
      <InertiaTable state={users} />
    </div>
  );
}
```

The component automatically displays:
- Skeleton loaders for the table structure
- Search input placeholder
- Pagination placeholders
- Smooth transition when data loads

## Customization

### Custom Styling

Apply custom CSS classes to the table:

```tsx
<InertiaTable 
  state={users}
  className="my-custom-table shadow-xl rounded-2xl border-2 border-blue-200"
/>
```

### Search Debouncing

Customize search input debouncing:

```tsx
<InertiaTable 
  state={users}
  searchDebounce={500} // Wait 500ms after user stops typing
/>
```

### Success and Error Handling

Provide custom notification handlers:

```tsx
import { toast } from 'sonner'; // or your preferred notification library

<InertiaTable 
  state={users}
  onSuccess={(message) => toast.success(message)}
  onError={(error) => toast.error(error)}
/>
```

## Hooks

### useInertiaTable Hook

Access table state and handlers directly:

```tsx
import { useInertiaTable } from '@tygoegmond/inertia-tables-react';

export default function CustomTable({ users }) {
  const {
    searchValue,
    handleSearch,
    handleSort,
    handlePageChange,
    isNavigating,
  } = useInertiaTable({
    initialSearch: users?.search || '',
    tableState: users,
  });

  return (
    <div>
      {/* Custom search input */}
      <input
        type="text"
        value={searchValue}
        onChange={(e) => handleSearch(e.target.value)}
        placeholder="Search users..."
        className="mb-4 p-2 border rounded"
      />
      
      {/* Custom sort buttons */}
      <div className="mb-4 space-x-2">
        <button 
          onClick={() => handleSort('name', 'asc')}
          className="px-3 py-1 bg-blue-500 text-white rounded"
        >
          Sort by Name ↑
        </button>
        <button 
          onClick={() => handleSort('created_at', 'desc')}
          className="px-3 py-1 bg-blue-500 text-white rounded"
        >
          Sort by Date ↓
        </button>
      </div>
      
      {/* Loading indicator */}
      {isNavigating && (
        <div className="mb-4 text-blue-600">Loading...</div>
      )}
      
      {/* Rest of your custom table */}
    </div>
  );
}
```

### useTableActions Hook

Handle actions programmatically:

```tsx
import { useTableActions } from '@tygoegmond/inertia-tables-react';

export default function CustomActionsTable({ users }) {
  const {
    executeAction,
    executeBulkAction,
    executeHeaderAction,
    isLoading,
    confirmationDialog,
    confirmAction,
    cancelAction,
  } = useTableActions({
    tableName: users.name,
    primaryKey: 'id',
    onSuccess: (message) => toast.success(message),
    onError: (error) => toast.error(error),
  });

  const handleCustomAction = (actionName: string, record: any) => {
    executeAction(actionName, record);
  };

  return (
    <div>
      {/* Your custom action buttons */}
      <button 
        onClick={() => handleCustomAction('approve', someRecord)}
        disabled={isLoading}
        className="px-4 py-2 bg-green-500 text-white rounded"
      >
        {isLoading ? 'Processing...' : 'Approve'}
      </button>
      
      {/* Confirmation dialog is handled automatically */}
      {confirmationDialog && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
          <div className="bg-white p-6 rounded-lg">
            <h3 className="text-lg font-bold mb-2">{confirmationDialog.title}</h3>
            <p className="mb-4">{confirmationDialog.message}</p>
            <div className="flex space-x-2">
              <button 
                onClick={confirmAction}
                className="px-4 py-2 bg-red-500 text-white rounded"
              >
                {confirmationDialog.confirmButton}
              </button>
              <button 
                onClick={cancelAction}
                className="px-4 py-2 bg-gray-300 rounded"
              >
                {confirmationDialog.cancelButton}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
```

## Custom Column Renderers

Create custom renderers for specific column types:

```tsx
// Custom avatar column renderer
const AvatarColumn = ({ value, config }) => (
  <img 
    src={value || '/default-avatar.png'} 
    alt="Avatar"
    className={`${config.rounded ? 'rounded-full' : 'rounded'}`}
    style={{ width: config.size, height: config.size }}
  />
);

// Status badge renderer
const StatusColumn = ({ value }) => {
  const colors = {
    active: 'bg-green-100 text-green-800',
    inactive: 'bg-gray-100 text-gray-800',
    pending: 'bg-yellow-100 text-yellow-800',
  };
  
  return (
    <span className={`px-2 py-1 rounded-full text-xs ${colors[value.status] || colors.inactive}`}>
      {value.icon} {value.text}
    </span>
  );
};

// Register custom renderers
const customColumnRenderers = {
  avatar: AvatarColumn,
  status: StatusColumn,
};

export default function UsersIndex({ users }) {
  return (
    <InertiaTable 
      state={users}
      customColumnRenderers={customColumnRenderers}
    />
  );
}
```

## Custom Action Renderers

Create custom action button components:

```tsx
// Custom email action renderer
const EmailActionButton = ({ action, onExecute, disabled }) => (
  <button
    onClick={() => onExecute(action.name)}
    disabled={disabled}
    className="inline-flex items-center px-3 py-1 text-sm bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50"
  >
    <MailIcon className="w-4 h-4 mr-1" />
    {action.label}
  </button>
);

// Custom workflow action with progress
const WorkflowActionButton = ({ action, onExecute, disabled }) => {
  const [isProcessing, setIsProcessing] = useState(false);
  
  const handleClick = async () => {
    setIsProcessing(true);
    await onExecute(action.name);
    setIsProcessing(false);
  };
  
  return (
    <button
      onClick={handleClick}
      disabled={disabled || isProcessing}
      className="inline-flex items-center px-3 py-1 text-sm bg-purple-500 text-white rounded"
    >
      {isProcessing ? (
        <Spinner className="w-4 h-4 mr-1" />
      ) : (
        <PlayIcon className="w-4 h-4 mr-1" />
      )}
      {isProcessing ? 'Processing...' : action.label}
    </button>
  );
};

const customActionRenderers = {
  email: EmailActionButton,
  workflow: WorkflowActionButton,
};

export default function UsersIndex({ users }) {
  return (
    <InertiaTable 
      state={users}
      customActionRenderers={customActionRenderers}
    />
  );
}
```

## TypeScript Support

### Strongly Typed Table Data

Define interfaces for your table data:

```tsx
interface User {
  id: number;
  name: string;
  email: string;
  status: 'active' | 'inactive' | 'pending';
  created_at: string;
}

interface UserTableResult extends TableResult<User> {
  // Additional type-specific properties
}

interface UsersPageProps {
  users: UserTableResult;
}

export default function UsersPage({ users }: UsersPageProps) {
  return <InertiaTable<User> state={users} />;
}
```

### Custom Component Types

Type your custom renderers properly:

```tsx
interface CustomColumnProps<T = any> {
  value: any;
  record: T;
  config: TableColumn;
}

interface CustomActionProps {
  action: TableAction;
  onExecute: (actionName: string, data?: any) => void;
  disabled?: boolean;
}

const TypedAvatarColumn: React.FC<CustomColumnProps<User>> = ({ 
  value, 
  record, 
  config 
}) => (
  <img 
    src={value || record.defaultAvatar} 
    alt={`${record.name}'s avatar`}
    className="rounded-full"
    style={{ width: config.size, height: config.size }}
  />
);
```

## Advanced Patterns

### Custom Table Layout

Build your own table layout using the hooks:

```tsx
import { useInertiaTable, useTableActions } from '@tygoegmond/inertia-tables-react';

export default function CustomTableLayout({ users }) {
  const { searchValue, handleSearch, handleSort, isNavigating } = useInertiaTable({
    tableState: users,
  });
  
  const { executeAction } = useTableActions({
    tableName: users.name,
    primaryKey: 'id',
  });

  return (
    <div className="space-y-6">
      {/* Custom Header */}
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-bold">Users</h1>
        <div className="flex space-x-4">
          <input
            type="text"
            value={searchValue}
            onChange={(e) => handleSearch(e.target.value)}
            placeholder="Search users..."
            className="px-3 py-2 border rounded-lg"
          />
          <button className="px-4 py-2 bg-blue-500 text-white rounded-lg">
            Add User
          </button>
        </div>
      </div>

      {/* Custom Filters */}
      <div className="bg-gray-50 p-4 rounded-lg">
        {/* Your custom filter components */}
      </div>

      {/* Loading State */}
      {isNavigating && (
        <div className="text-center py-8">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
          <p className="mt-2 text-gray-600">Loading...</p>
        </div>
      )}

      {/* Custom Table */}
      <div className="bg-white rounded-lg shadow overflow-hidden">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              {users.config.columns.map((column) => (
                <th
                  key={column.key}
                  className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                  onClick={() => handleSort(column.key, 'asc')}
                >
                  {column.label}
                </th>
              ))}
              <th className="px-6 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {users.data.map((user) => (
              <tr key={user.id}>
                <td className="px-6 py-4 whitespace-nowrap">{user.name}</td>
                <td className="px-6 py-4 whitespace-nowrap">{user.email}</td>
                <td className="px-6 py-4 whitespace-nowrap text-right">
                  <button
                    onClick={() => executeAction('edit', user)}
                    className="text-blue-600 hover:text-blue-900"
                  >
                    Edit
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* Custom Pagination */}
      <div className="flex items-center justify-between">
        <div className="text-sm text-gray-700">
          Showing {users.pagination.from} to {users.pagination.to} of{' '}
          {users.pagination.total} results
        </div>
        <div className="flex space-x-2">
          {/* Your custom pagination buttons */}
        </div>
      </div>
    </div>
  );
}
```

### Responsive Tables

Handle responsive design with custom layouts:

```tsx
export default function ResponsiveUsersTable({ users }) {
  const [isMobile, setIsMobile] = useState(false);
  
  useEffect(() => {
    const checkMobile = () => setIsMobile(window.innerWidth < 768);
    checkMobile();
    window.addEventListener('resize', checkMobile);
    return () => window.removeEventListener('resize', checkMobile);
  }, []);

  if (isMobile) {
    return (
      <div className="space-y-4">
        {users.data.map((user) => (
          <div key={user.id} className="bg-white p-4 rounded-lg shadow">
            <div className="flex justify-between items-start">
              <div>
                <h3 className="font-semibold">{user.name}</h3>
                <p className="text-gray-600">{user.email}</p>
                <p className="text-sm text-gray-500">{user.created_at}</p>
              </div>
              <div className="space-x-2">
                {/* Mobile action buttons */}
              </div>
            </div>
          </div>
        ))}
      </div>
    );
  }

  return <InertiaTable state={users} />;
}
```

## Performance Optimization

### Memoization

Optimize renders with React.memo and useMemo:

```tsx
import React, { memo, useMemo } from 'react';

const CustomTableRow = memo<{ user: User; actions: TableAction[] }>(
  ({ user, actions }) => {
    const memoizedActions = useMemo(
      () => actions.filter(action => action.visible !== false),
      [actions]
    );

    return (
      <tr>
        <td>{user.name}</td>
        <td>{user.email}</td>
        <td>
          {memoizedActions.map((action) => (
            <ActionButton key={action.name} action={action} record={user} />
          ))}
        </td>
      </tr>
    );
  }
);

const OptimizedUsersTable = memo<{ users: TableResult<User> }>(
  ({ users }) => {
    const memoizedColumns = useMemo(
      () => users.config.columns.filter(col => col.visible !== false),
      [users.config.columns]
    );

    return (
      <InertiaTable 
        state={users}
        customColumnRenderers={memoizedColumns}
      />
    );
  }
);
```

### Virtualization

For very large tables, consider virtualization:

```tsx
import { FixedSizeList as List } from 'react-window';

const VirtualizedTable = ({ users }) => {
  const Row = ({ index, style }) => {
    const user = users.data[index];
    return (
      <div style={style} className="flex items-center p-4 border-b">
        <div className="flex-1">{user.name}</div>
        <div className="flex-1">{user.email}</div>
        <div className="flex-none">
          {/* Actions */}
        </div>
      </div>
    );
  };

  return (
    <List
      height={600}
      itemCount={users.data.length}
      itemSize={60}
    >
      {Row}
    </List>
  );
};
```

## Testing

### Component Testing

Test your table components with React Testing Library:

```tsx
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { InertiaTable } from '@tygoegmond/inertia-tables-react';

const mockTableData = {
  config: {
    columns: [
      { key: 'name', label: 'Name', type: 'text', visible: true, sortable: true },
      { key: 'email', label: 'Email', type: 'text', visible: true, sortable: true },
    ],
    searchable: true,
  },
  data: [
    { id: 1, name: 'John Doe', email: 'john@example.com' },
    { id: 2, name: 'Jane Smith', email: 'jane@example.com' },
  ],
  pagination: {
    current_page: 1,
    total: 2,
    per_page: 10,
    last_page: 1,
  },
  name: 'users',
};

describe('InertiaTable', () => {
  test('renders table with data', () => {
    render(<InertiaTable state={mockTableData} />);
    
    expect(screen.getByText('John Doe')).toBeInTheDocument();
    expect(screen.getByText('jane@example.com')).toBeInTheDocument();
  });

  test('handles search input', async () => {
    render(<InertiaTable state={mockTableData} />);
    
    const searchInput = screen.getByPlaceholderText('Search...');
    fireEvent.change(searchInput, { target: { value: 'John' } });
    
    await waitFor(() => {
      expect(searchInput.value).toBe('John');
    });
  });

  test('handles column sorting', () => {
    render(<InertiaTable state={mockTableData} />);
    
    const nameHeader = screen.getByText('Name');
    fireEvent.click(nameHeader);
    
    // Test sorting logic
  });
});
```

### Hook Testing

Test custom hooks:

```tsx
import { renderHook, act } from '@testing-library/react-hooks';
import { useInertiaTable } from '@tygoegmond/inertia-tables-react';

describe('useInertiaTable', () => {
  test('handles search value changes', () => {
    const { result } = renderHook(() => 
      useInertiaTable({ tableState: mockTableData })
    );

    act(() => {
      result.current.handleSearch('test query');
    });

    expect(result.current.searchValue).toBe('test query');
  });
});
```

## Best Practices

### 1. Use TypeScript

Always use TypeScript for better development experience:

```tsx
// Good: Strongly typed
interface UsersPageProps {
  users: TableResult<User>;
}

// Bad: Any types
const UsersPage = ({ users }: any) => { ... }
```

### 2. Memoize Custom Renderers

Prevent unnecessary re-renders:

```tsx
// Good: Memoized component
const StatusBadge = memo(({ status }) => (
  <span className={`badge badge-${status}`}>{status}</span>
));

// Bad: Inline component
const customRenderers = {
  status: ({ value }) => <span className={`badge badge-${value}`}>{value}</span>
};
```

### 3. Handle Loading States

Provide feedback during data loading:

```tsx
<InertiaTable 
  state={users}
  onSuccess={(message) => toast.success(message)}
  onError={(error) => toast.error(error)}
/>
```

### 4. Optimize for Mobile

Consider responsive design:

```tsx
<InertiaTable 
  state={users}
  className="lg:table-fixed table-auto"
/>
```

## Next Steps

- **[Advanced Usage](/08-advanced-usage)** - Performance optimization and advanced patterns
- **[API Reference](/09-api-reference)** - Complete API documentation
- **[Examples](/10-examples)** - Real-world implementation examples

## React Integration Reference

### Main Components

| Component | Description | Required Props |
|-----------|-------------|----------------|
| `InertiaTable` | Main table component | `state: TableResult` |
| `DeferredTableLoader` | Loading state for deferred data | None |
| `ErrorBoundary` | Error handling wrapper | None |

### Hooks

| Hook | Description | Returns |
|------|-------------|---------|
| `useInertiaTable` | Table state management | `{ searchValue, handleSearch, handleSort, handlePageChange, isNavigating }` |
| `useTableActions` | Action handling | `{ executeAction, executeBulkAction, executeHeaderAction, isLoading, confirmationDialog }` |

### TypeScript Interfaces

```tsx
interface TableResult<T = any> {
  config: TableConfig;
  data: T[];
  pagination: TablePagination;
  sort: Record<string, 'asc' | 'desc'>;
  search: string | null;
  name: string;
  actions: TableAction[];
  bulkActions: TableBulkAction[];
  headerActions: TableAction[];
}
```