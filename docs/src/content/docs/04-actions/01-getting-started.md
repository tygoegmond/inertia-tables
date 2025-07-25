---
title: Getting Started with Actions
description: Learn about the action system in Inertia Tables and how to add interactive functionality to your tables.
---

## Overview

Actions in Inertia Tables provide interactive functionality that allows users to perform operations on table data. The action system supports three types of actions:

1. **Row Actions** - Actions that operate on individual table rows
2. **Bulk Actions** - Actions that operate on multiple selected rows
3. **Header Actions** - Actions that operate at the table level (not tied to specific rows)

## Action Types

### Row Actions

Row actions appear for each row in your table, typically as buttons in an actions column:

```php
use Egmond\InertiaTables\Actions\Action;

->actions([
    Action::make('edit')
        ->label('Edit')
        ->color('primary'),
    
    Action::make('delete')
        ->label('Delete')
        ->color('danger')
        ->requiresConfirmation(),
])
```

### Bulk Actions  

Bulk actions operate on multiple selected rows and appear when rows are selected:

```php
use Egmond\InertiaTables\Actions\BulkAction;

->bulkActions([
    BulkAction::make('delete')
        ->label('Delete Selected')
        ->color('danger')
        ->requiresConfirmation()
        ->authorize(fn() => auth()->user()->can('delete-users')),
])
```

### Header Actions

Header actions appear in the table header and operate at the table level:

```php
->headerActions([
    Action::make('create')
        ->label('Create User')
        ->color('primary'),
        
    Action::make('export')
        ->label('Export CSV')
        ->color('secondary'),
])
```

## Basic Action Configuration

### Creating Actions

All actions are created using the `make()` method:

```php
Action::make('edit')           // Row/Header action
BulkAction::make('delete')     // Bulk action
```

### Action Labels

Set display text for actions:

```php
Action::make('edit')
    ->label('Edit User')
```

### Action Colors

Actions support several color variants:

```php
Action::make('save')
    ->color('primary')      // Blue (default)
    ->primary()             // Shorthand

Action::make('delete')
    ->color('danger')       // Red  
    ->danger()              // Shorthand

Action::make('approve')
    ->success()             // Green

Action::make('warn')
    ->warning()             // Yellow

Action::make('info')
    ->info()                // Light blue

Action::make('archive')
    ->gray()                // Gray
```

## Action Handlers

### Defining Action Methods

Actions need corresponding handler methods in your table class:

```php
class UserTable implements HasTable
{
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->actions([
                Action::make('edit'),
                Action::make('delete')->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkAction::make('delete')
                    ->authorize(fn() => auth()->user()->can('delete-users')),
            ]);
    }

    // Row action handlers
    public function editAction($record)
    {
        return redirect()->route('users.edit', $record);
    }

    public function deleteAction($record)
    {
        $record->delete();
        return back()->with('success', 'User deleted successfully');
    }

    // Bulk action handlers  
    public function deleteBulkAction($records)
    {
        foreach ($records as $record) {
            $record->delete();
        }
        return back()->with('success', count($records) . ' users deleted');
    }

    // Header action handlers
    public function createAction()
    {
        return redirect()->route('users.create');
    }
}
```

### Action Method Naming

Action handlers follow a naming convention:

- Row actions: `{actionName}Action($record)`
- Bulk actions: `{actionName}BulkAction($records)`  
- Header actions: `{actionName}Action()` (no parameters)

## Confirmation Dialogs

Actions can require user confirmation before executing:

```php
Action::make('delete')
    ->requiresConfirmation()
    ->confirmationTitle('Delete User')
    ->confirmationMessage('Are you sure you want to delete this user? This action cannot be undone.')
    ->confirmationButton('Delete')
    ->cancelButton('Cancel')
```

### Shorthand Confirmation

For simple confirmations, use the shorthand:

```php
Action::make('delete')
    ->requiresConfirmation('Delete User', 'This action cannot be undone.')
```

## Authorization

Control who can see and execute actions:

```php
Action::make('delete')
    ->authorize(fn($record) => auth()->user()->can('delete', $record))

BulkAction::make('delete')
    ->authorize(fn() => auth()->user()->can('bulk-delete-users'))

Action::make('admin-only')
    ->authorize(fn() => auth()->user()->isAdmin())
```

## Visibility Control

Show or hide actions based on conditions:

```php
Action::make('activate')
    ->visible(fn($record) => !$record->is_active)

Action::make('deactivate')  
    ->visible(fn($record) => $record->is_active)

Action::make('edit')
    ->hidden(fn($record) => $record->is_locked)
```

## Conditional Styling

Change action appearance based on conditions:

```php
Action::make('status')
    ->label(fn($record) => $record->is_active ? 'Deactivate' : 'Activate')
    ->color(fn($record) => $record->is_active ? 'danger' : 'success')
```

## Action Routing

### Direct Routes

Actions can redirect to specific routes:

```php
public function editAction($record)
{
    return redirect()->route('users.edit', $record);
}

public function showAction($record)
{
    return redirect()->route('users.show', $record);
}
```

### Inertia Responses

For single-page applications, return Inertia responses:

```php
public function editAction($record)
{
    return Inertia::render('Users/Edit', [
        'user' => $record,
    ]);
}
```

### JSON Responses

Return JSON for AJAX operations:

```php
public function toggleAction($record)
{
    $record->update(['is_active' => !$record->is_active]);
    
    return response()->json([
        'success' => true,
        'message' => 'Status updated successfully',
    ]);
}
```

## Complete Example

Here's a comprehensive example showing all action types:

```php
<?php

namespace App\Tables;

use App\Models\User;
use Egmond\InertiaTables\Actions\Action;
use Egmond\InertiaTables\Actions\BulkAction;
use Egmond\InertiaTables\Concerns\InteractsWithTable;
use Egmond\InertiaTables\Contracts\HasTable;
use Egmond\InertiaTables\Table;

class UserTable implements HasTable
{
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table->as('users')
            ->query(User::query())
            ->columns([
                // ... columns
            ])
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->color('secondary'),
                    
                Action::make('edit')
                    ->label('Edit')
                    ->authorize(fn($record) => auth()->user()->can('update', $record)),
                    
                Action::make('delete')
                    ->label('Delete')
                    ->color('danger')
                    ->requiresConfirmation('Delete User', 'This action cannot be undone.')
                    ->authorize(fn($record) => auth()->user()->can('delete', $record)),
            ])
            ->bulkActions([
                BulkAction::make('activate')
                    ->label('Activate Selected')
                    ->color('success')
                    ->authorize(fn() => auth()->user()->can('bulk-update-users')),
                    
                BulkAction::make('delete')
                    ->label('Delete Selected')
                    ->color('danger')
                    ->requiresConfirmation('Delete Users', 'This will permanently delete all selected users.')
                    ->authorize(fn() => auth()->user()->can('bulk-delete-users')),
            ])
            ->headerActions([
                Action::make('create')
                    ->label('Create User')
                    ->color('primary'),
                    
                Action::make('export')
                    ->label('Export CSV')
                    ->color('secondary'),
            ]);
    }

    // Row action handlers
    public function viewAction($record)
    {
        return redirect()->route('users.show', $record);
    }

    public function editAction($record)
    {
        return redirect()->route('users.edit', $record);
    }

    public function deleteAction($record)
    {
        $record->delete();
        return back()->with('success', 'User deleted successfully');
    }

    // Bulk action handlers
    public function activateBulkAction($records)
    {
        User::whereIn('id', collect($records)->pluck('id'))
            ->update(['is_active' => true]);
            
        return back()->with('success', count($records) . ' users activated');
    }

    public function deleteBulkAction($records)
    {
        User::whereIn('id', collect($records)->pluck('id'))->delete();
        return back()->with('success', count($records) . ' users deleted');
    }

    // Header action handlers
    public function createAction()
    {
        return redirect()->route('users.create');
    }

    public function exportAction()
    {
        // Export logic here
        return response()->download($csvPath);
    }
}
```

## Frontend Integration

Actions automatically integrate with the React frontend:

```tsx
import { InertiaTable } from '@tygoegmond/inertia-tables-react';

export default function UsersIndex({ users }) {
  return (
    <div>
      <h1>Users</h1>
      <InertiaTable state={users} />
    </div>
  );
}
```

The table will automatically render:
- Action buttons in each row
- Bulk action toolbar when rows are selected
- Header actions in the table toolbar

## Next Steps

Explore specific action types in detail:

- **[Row Actions](/04-actions/02-row-actions)** - Individual row operations
- **[Bulk Actions](/04-actions/03-bulk-actions)** - Multi-row operations  
- **[Header Actions](/04-actions/04-header-actions)** - Table-level operations
- **[Custom Actions](/04-actions/05-custom-actions)** - Building custom action types

## Action Reference

### Common Action Methods

| Method | Description | Example |
|--------|-------------|---------|
| `make(string $name)` | Create a new action | `Action::make('edit')` |
| `label(string $label)` | Set action label | `->label('Edit User')` |
| `color(string $color)` | Set color variant | `->color('danger')` |
| `authorize(Closure $callback)` | Set authorization | `->authorize(fn($r) => ...)` |
| `visible(bool\|Closure $visible)` | Control visibility | `->visible(false)` |
| `requiresConfirmation()` | Add confirmation dialog | `->requiresConfirmation()` |

### Color Shortcuts

| Method | Color | Use Case |
|--------|-------|----------|
| `->primary()` | Blue | Main actions |
| `->success()` | Green | Positive actions |
| `->danger()` | Red | Destructive actions |
| `->warning()` | Yellow | Caution actions |
| `->info()` | Light blue | Informational |
| `->gray()` | Gray | Secondary actions |