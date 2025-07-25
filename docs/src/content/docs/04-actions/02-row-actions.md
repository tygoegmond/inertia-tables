---
title: Row Actions
description: Learn how to create and configure row actions that operate on individual table records.
---

## Overview

Row actions are interactive buttons that appear for each row in your table, allowing users to perform operations on individual records. They typically appear in a dedicated actions column at the end of each row.

## Basic Row Actions

### Creating Row Actions

```php
use Egmond\InertiaTables\Actions\Action;

->actions([
    Action::make('view')
        ->label('View')
        ->color('secondary'),
        
    Action::make('edit')
        ->label('Edit')
        ->color('primary'),
        
    Action::make('delete')
        ->label('Delete')
        ->color('danger')
        ->requiresConfirmation(),
])
```

### Action Handlers

Define corresponding handler methods in your table class:

```php
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
```

## Advanced Row Action Features

### Conditional Actions

Show different actions based on record state:

```php
->actions([
    Action::make('activate')
        ->label('Activate')
        ->color('success')
        ->visible(fn($record) => !$record->is_active),
        
    Action::make('deactivate')
        ->label('Deactivate')
        ->color('warning')
        ->visible(fn($record) => $record->is_active),
        
    Action::make('edit')
        ->label('Edit')
        ->hidden(fn($record) => $record->is_locked),
])
```

### Dynamic Labels and Colors

```php
Action::make('toggle_status')
    ->label(function ($record) {
        return $record->is_active ? 'Deactivate' : 'Activate';
    })
    ->color(function ($record) {
        return $record->is_active ? 'warning' : 'success';
    })
```

### Record-Specific Authorization

```php
Action::make('edit')
    ->authorize(function ($record) {
        return auth()->user()->can('update', $record);
    })

Action::make('delete')
    ->authorize(function ($record) {
        // Only allow deleting own records or if admin
        return $record->user_id === auth()->id() || auth()->user()->isAdmin();
    })
```

## Confirmation Dialogs

### Basic Confirmation

```php
Action::make('delete')
    ->requiresConfirmation()
    ->confirmationTitle('Delete User')
    ->confirmationMessage('Are you sure you want to delete this user?')
```

### Dynamic Confirmation Messages

```php
Action::make('delete')
    ->requiresConfirmation()
    ->confirmationTitle('Delete User')
    ->confirmationMessage(function ($record) {
        return "Are you sure you want to delete {$record->name}? This action cannot be undone.";
    })
    ->confirmationButton('Yes, Delete')
    ->cancelButton('Cancel')
```

## Complex Row Action Examples

### Status Toggle with Feedback

```php
// In your table configuration
Action::make('toggle_status')
    ->label(fn($record) => $record->is_active ? 'Deactivate' : 'Activate')
    ->color(fn($record) => $record->is_active ? 'warning' : 'success')

// Handler method
public function toggleStatusAction($record)
{
    $newStatus = !$record->is_active;
    $record->update(['is_active' => $newStatus]);
    
    $message = $newStatus ? 'User activated successfully' : 'User deactivated successfully';
    return back()->with('success', $message);
}
```

### Send Email Action

```php
// Action configuration
Action::make('send_welcome')
    ->label('Send Welcome Email')
    ->color('primary')
    ->visible(fn($record) => !$record->welcome_email_sent)
    ->authorize(fn($record) => auth()->user()->can('send-emails'))

// Handler method
public function sendWelcomeAction($record)
{
    Mail::to($record->email)->send(new WelcomeEmail($record));
    
    $record->update(['welcome_email_sent' => true]);
    
    return back()->with('success', 'Welcome email sent successfully');
}
```

### Clone Record Action

```php
Action::make('clone')
    ->label('Clone')
    ->color('secondary')
    ->authorize(fn($record) => auth()->user()->can('create', get_class($record)))

public function cloneAction($record)
{
    $cloned = $record->replicate();
    $cloned->name = $record->name . ' (Copy)';
    $cloned->save();
    
    return redirect()->route('users.edit', $cloned)
        ->with('success', 'User cloned successfully');
}
```

## Row Action Groups

Organize related actions into dropdown menus:

```php
->actions([
    // Primary actions (always visible)
    Action::make('edit')
        ->label('Edit')
        ->color('primary'),
        
    // Secondary actions (in dropdown)
    ActionGroup::make([
        Action::make('clone')
            ->label('Clone')
            ->color('secondary'),
            
        Action::make('export')
            ->label('Export Data')
            ->color('secondary'),
            
        Action::make('archive')
            ->label('Archive')
            ->color('warning'),
            
        Action::make('delete')
            ->label('Delete')
            ->color('danger')
            ->requiresConfirmation(),
    ])->label('More Actions'),
])
```

## Handling Different Response Types

### Redirect Responses

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

```php
public function editAction($record)
{
    return Inertia::render('Users/Edit', [
        'user' => $record,
    ]);
}
```

### JSON Responses

```php
public function quickUpdateAction($record)
{
    $record->increment('priority');
    
    return response()->json([
        'success' => true,
        'message' => 'Priority updated',
        'newValue' => $record->fresh()->priority,
    ]);
}
```

### File Downloads

```php
public function downloadAction($record)
{
    return response()->download(
        storage_path("app/exports/{$record->export_file}")
    );
}
```

## Row Action Styling

### Button Sizes

Row actions automatically use appropriate button sizes, but you can customize them:

```php
Action::make('edit')
    ->size('sm')     // Small button
    ->size('md')     // Medium (default)
    ->size('lg')     // Large button
```

### Icon-Only Actions

```php
Action::make('edit')
    ->icon('pencil')
    ->tooltip('Edit User')  // Shows on hover
    ->hideLabel()           // Hide text, show only icon
```

## Performance Considerations

### Efficient Authorization Queries

```php
// Instead of N+1 queries in authorization
Action::make('edit')
    ->authorize(function ($record) {
        return auth()->user()->can('update', $record); // N+1 problem
    })

// Pre-authorize at the table level
public function table(Table $table): Table
{
    $canEdit = auth()->user()->can('update-users');
    
    return $table->actions([
        Action::make('edit')
            ->visible($canEdit)
    ]);
}
```

### Lazy Loading Related Data

```php
// If actions need relationship data, eager load it
public function table(Table $table): Table
{
    return $table
        ->query(User::with('profile', 'permissions'))
        ->actions([
            Action::make('edit_profile')
                ->visible(fn($record) => $record->profile !== null)
        ]);
}
```

## Testing Row Actions

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Tables\UserTable;
use Tests\TestCase;

class UserTableActionsTest extends TestCase
{
    public function test_user_can_edit_own_record()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $table = UserTable::make();
        
        $response = $table->editAction($user);
        
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }
    
    public function test_delete_action_removes_record()
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        
        $table = UserTable::make();
        $table->deleteAction($user);
        
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
```

## Next Steps

- **[Bulk Actions](/04-actions/03-bulk-actions)** - Actions that operate on multiple selected rows
- **[Header Actions](/04-actions/04-header-actions)** - Table-level actions
- **[Custom Actions](/04-actions/05-custom-actions)** - Build your own action types

## Row Action Reference

### Configuration Methods

| Method | Description | Example |
|--------|-------------|--------|
| `visible(Closure $callback)` | Show/hide based on record | `->visible(fn($r) => $r->is_active)` |
| `hidden(Closure $callback)` | Hide based on record | `->hidden(fn($r) => $r->is_locked)` |
| `authorize(Closure $callback)` | Authorize based on record | `->authorize(fn($r) => auth()->user()->can('edit', $r))` |
| `label(string\|Closure $label)` | Dynamic label | `->label(fn($r) => $r->is_active ? 'Deactivate' : 'Activate')` |
| `color(string\|Closure $color)` | Dynamic color | `->color(fn($r) => $r->is_active ? 'warning' : 'success')` |

### Handler Method Signature

```php
public function {actionName}Action($record)
{
    // $record is the Eloquent model instance
    // Return redirect, Inertia response, JSON, or file download
}
```