---
title: Bulk Actions
description: Learn how to create bulk actions that operate on multiple selected table records simultaneously.
---

## Overview

Bulk actions allow users to perform operations on multiple selected rows simultaneously. They appear in a toolbar when one or more rows are selected, providing an efficient way to manage large datasets.

## Basic Bulk Actions

### Creating Bulk Actions

```php
use Egmond\InertiaTables\Actions\BulkAction;

->bulkActions([
    BulkAction::make('delete')
        ->label('Delete Selected')
        ->color('danger')
        ->requiresConfirmation()
        ->authorize(fn() => auth()->user()->can('bulk-delete-users')),
        
    BulkAction::make('activate')
        ->label('Activate Selected')
        ->color('success')
        ->authorize(fn() => auth()->user()->can('bulk-update-users')),
])
```

### Bulk Action Handlers

Define handler methods that receive an array of selected records:

```php
public function deleteBulkAction($records)
{
    foreach ($records as $record) {
        $record->delete();
    }
    
    return back()->with('success', count($records) . ' users deleted successfully');
}

public function activateBulkAction($records)
{
    $ids = collect($records)->pluck('id');
    
    User::whereIn('id', $ids)->update(['is_active' => true]);
    
    return back()->with('success', count($records) . ' users activated');
}
```

## Bulk Action Security

### Required Authorization

**Important**: Bulk actions require explicit authorization for security purposes. They will throw an exception if no `authorize()` method is defined:

```php
BulkAction::make('delete')
    ->authorize(function () {
        return auth()->user()->can('bulk-delete-users');
    })
```

### Per-Record Authorization

For fine-grained control, authorize each record individually:

```php
BulkAction::make('delete')
    ->authorize(function () {
        return auth()->user()->can('delete-users');
    })

public function deleteBulkAction($records)
{
    $authorized = [];
    $unauthorized = 0;
    
    foreach ($records as $record) {
        if (auth()->user()->can('delete', $record)) {
            $authorized[] = $record;
        } else {
            $unauthorized++;
        }
    }
    
    foreach ($authorized as $record) {
        $record->delete();
    }
    
    $message = count($authorized) . ' users deleted';
    if ($unauthorized > 0) {
        $message .= " ({$unauthorized} skipped due to permissions)";
    }
    
    return back()->with('success', $message);
}
```

## Advanced Bulk Action Examples

### Bulk Status Update

```php
BulkAction::make('update_status')
    ->label('Update Status')
    ->color('primary')
    ->authorize(fn() => auth()->user()->can('bulk-update-users'))

public function updateStatusBulkAction($records)
{
    // You could also accept additional parameters from a form
    $status = request('status', 'active');
    
    $ids = collect($records)->pluck('id');
    User::whereIn('id', $ids)->update(['status' => $status]);
    
    return back()->with('success', count($records) . " users updated to {$status}");
}
```

### Bulk Assignment

```php
BulkAction::make('assign_role')
    ->label('Assign Role')
    ->color('primary')
    ->authorize(fn() => auth()->user()->can('assign-roles'))

public function assignRoleBulkAction($records)
{
    $roleId = request('role_id');
    $role = Role::findOrFail($roleId);
    
    foreach ($records as $record) {
        $record->assignRole($role);
    }
    
    return back()->with('success', count($records) . " users assigned to {$role->name}");
}
```

### Bulk Export

```php
BulkAction::make('export')
    ->label('Export Selected')
    ->color('secondary')
    ->authorize(fn() => auth()->user()->can('export-users'))

public function exportBulkAction($records)
{
    $ids = collect($records)->pluck('id');
    
    // Generate CSV or Excel file\n    $filename = 'selected_users_' . now()->format('Y-m-d_H-i-s') . '.csv';\n    $path = storage_path('app/exports/' . $filename);\n    \n    // Create CSV content\n    $csv = Writer::createFromPath($path, 'w+');\n    $csv->insertOne(['Name', 'Email', 'Created At']);\n    \n    User::whereIn('id', $ids)->each(function($user) use ($csv) {\n        $csv->insertOne([$user->name, $user->email, $user->created_at]);\n    });\n    \n    return response()->download($path)->deleteFileAfterSend();\n}
```

### Bulk Email Sending

```php
BulkAction::make('send_notification')
    ->label('Send Notification')  
    ->color('primary')
    ->authorize(fn() => auth()->user()->can('send-bulk-emails'))

public function sendNotificationBulkAction($records)
{
    $message = request('message');
    $subject = request('subject', 'Important Notification');
    
    $sent = 0;\n    foreach ($records as $record) {\n        if ($record->email && $record->accepts_notifications) {\n            Mail::to($record->email)->send(new BulkNotification($subject, $message));\n            $sent++;\n        }\n    }\n    \n    return back()->with('success', "Notification sent to {$sent} users");\n}
```

## Bulk Action Confirmation

### Simple Confirmation

```php
BulkAction::make('delete')
    ->requiresConfirmation()
    ->confirmationTitle('Delete Users')
    ->confirmationMessage('Are you sure you want to delete the selected users?')
```

### Dynamic Confirmation Messages

```php
BulkAction::make('delete')
    ->requiresConfirmation()
    ->confirmationTitle('Delete Users')
    ->confirmationMessage(function ($records) {
        $count = count($records);\n        return \"Are you sure you want to delete {$count} users? This action cannot be undone.\";\n    })\n    ->confirmationButton('Yes, Delete All')\n    ->cancelButton('Cancel')
```

## Bulk Action Forms

### With Form Inputs

Some bulk actions may need additional input from users:

```php
BulkAction::make('assign_category')
    ->label('Assign Category')
    ->form([\n        Select::make('category_id')\n            ->label('Category')\n            ->options(Category::pluck('name', 'id'))\n            ->required(),\n    ])\n    ->authorize(fn() => auth()->user()->can('assign-categories'))

public function assignCategoryBulkAction($records)\n{\n    $categoryId = request('category_id');\n    $category = Category::findOrFail($categoryId);\n    \n    $ids = collect($records)->pluck('id');\n    Product::whereIn('id', $ids)->update(['category_id' => $categoryId]);\n    \n    return back()->with('success', count($records) . \" products assigned to {$category->name}\");\n}
```

## Performance Optimization

### Efficient Database Operations

```php
public function activateBulkAction($records)
{
    // Efficient: Single query update
    $ids = collect($records)->pluck('id');
    User::whereIn('id', $ids)->update(['is_active' => true]);
    
    // Less efficient: Individual updates
    // foreach ($records as $record) {
    //     $record->update(['is_active' => true]);\n    // }\n    \n    return back()->with('success', count($records) . ' users activated');\n}
```

### Chunked Processing

For large datasets, process records in chunks:

```php
public function processBulkAction($records)\n{\n    $ids = collect($records)->pluck('id');\n    $processed = 0;\n    \n    User::whereIn('id', $ids)->chunk(100, function ($users) use (&$processed) {\n        foreach ($users as $user) {\n            // Process each user\n            $this->processUser($user);\n            $processed++;\n        }\n    });\n    \n    return back()->with('success', \"{$processed} users processed successfully\");\n}
```

### Queue Integration

For time-intensive operations, dispatch to queues:

```php
public function processLargeBulkAction($records)\n{\n    $ids = collect($records)->pluck('id');\n    \n    ProcessUsersBulk::dispatch($ids);\n    \n    return back()->with('info', 'Processing started. You will be notified when complete.');\n}
```

## Error Handling

### Graceful Error Handling

```php
public function deleteBulkAction($records)\n{\n    $deleted = 0;\n    $errors = 0;\n    $errorMessages = [];\n    \n    foreach ($records as $record) {\n        try {\n            if ($record->posts()->count() > 0) {\n                throw new \\Exception('User has posts and cannot be deleted');\n            }\n            \n            $record->delete();\n            $deleted++;\n        } catch (\\Exception $e) {\n            $errors++;\n            $errorMessages[] = \"Failed to delete {$record->name}: {$e->getMessage()}\";\n        }\n    }\n    \n    $message = \"{$deleted} users deleted successfully\";\n    if ($errors > 0) {\n        $message .= \". {$errors} errors occurred.\";\n        session()->flash('errors', $errorMessages);\n    }\n    \n    return back()->with($deleted > 0 ? 'success' : 'error', $message);\n}
```

## Testing Bulk Actions

```php
<?php

namespace Tests\\Feature;

use App\\Models\\User;
use App\\Tables\\UserTable;
use Tests\\TestCase;

class BulkActionsTest extends TestCase
{
    public function test_bulk_delete_removes_multiple_users()
    {
        $admin = User::factory()->admin()->create();
        $users = User::factory()->count(3)->create();
        
        $this->actingAs($admin);
        
        $table = UserTable::make();
        $table->deleteBulkAction($users->toArray());
        
        foreach ($users as $user) {
            $this->assertDatabaseMissing('users', ['id' => $user->id]);
        }
    }
    
    public function test_bulk_action_requires_authorization()
    {
        $user = User::factory()->create(); // Regular user\n        $users = User::factory()->count(2)->create();\n        \n        $this->actingAs($user);\n        \n        $table = UserTable::make();\n        \n        $this->expectException(\\Illuminate\\Auth\\Access\\AuthorizationException::class);\n        \n        $table->deleteBulkAction($users->toArray());\n    }\n}
```

## Frontend Integration

The bulk actions automatically integrate with the React frontend:

```tsx
import { InertiaTable } from '@tygoegmond/inertia-tables-react';

export default function UsersIndex({ users }) {
  return (
    <InertiaTable 
      state={users}
      // Bulk actions appear automatically when rows are selected
    />
  );
}
```

Features include:
- Checkbox selection for rows
- "Select All" functionality
- Bulk action toolbar appears when rows are selected
- Confirmation dialogs for destructive actions
- Progress indicators for long-running operations

## Best Practices

### 1. Always Require Authorization

```php
// Good: Explicit authorization\nBulkAction::make('delete')\n    ->authorize(fn() => auth()->user()->can('bulk-delete-users'))

// Bad: No authorization (will throw exception)\nBulkAction::make('delete') // Missing authorize()
```

### 2. Provide Clear Feedback

```php
public function deleteBulkAction($records)\n{\n    $count = count($records);\n    \n    foreach ($records as $record) {\n        $record->delete();\n    }\n    \n    // Clear, specific feedback\n    return back()->with('success', \"{$count} users deleted successfully\");\n}
```

### 3. Handle Edge Cases

```php
public function processBulkAction($records)\n{\n    if (empty($records)) {\n        return back()->with('warning', 'No records selected');\n    }\n    \n    // Process records...\n}
```

### 4. Use Database Transactions

```php
public function complexBulkAction($records)\n{\n    DB::transaction(function () use ($records) {\n        foreach ($records as $record) {\n            // Multiple database operations\n            $record->update(['status' => 'processed']);\n            $record->logs()->create(['action' => 'bulk_processed']);\n        }\n    });\n    \n    return back()->with('success', 'Bulk operation completed');\n}
```

## Next Steps

- **[Header Actions](/04-actions/04-header-actions)** - Table-level actions
- **[Custom Actions](/04-actions/05-custom-actions)** - Build custom action types
- **[Search & Filtering](/05-search-and-filtering)** - Advanced search capabilities

## Bulk Action Reference

### Handler Method Signature

```php
public function {actionName}BulkAction($records)
{
    // $records is an array of Eloquent model instances
    // Always includes authorize() check before this method is called
}
```

### Security Requirements

| Requirement | Description | Example |
|-------------|-------------|---------|
| **Authorization Required** | All bulk actions must define authorization | `->authorize(fn() => auth()->user()->can('bulk-action'))` |
| **CSRF Protection** | Automatically handled by Inertia | Built-in |
| **Input Validation** | Validate any additional form inputs | `request()->validate(['status' => 'required'])` |