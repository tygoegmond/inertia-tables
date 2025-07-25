---
title: Header Actions
description: Learn how to create header actions that operate at the table level and provide table-wide functionality.
---

## Overview

Header actions appear in the table header/toolbar and provide table-level functionality that isn't tied to specific records. They're perfect for operations like creating new records, importing/exporting data, or accessing table-wide settings.

## Basic Header Actions

### Creating Header Actions

```php
use Egmond\InertiaTables\Actions\Action;

->headerActions([
    Action::make('create')
        ->label('Create User')
        ->color('primary'),
        
    Action::make('import')
        ->label('Import CSV')
        ->color('secondary'),
        
    Action::make('export')
        ->label('Export All')
        ->color('secondary'),
])
```

### Header Action Handlers

Header action handlers don't receive any parameters since they operate at the table level:

```php
public function createAction()
{
    return redirect()->route('users.create');
}

public function importAction()
{
    return Inertia::render('Users/Import', [
        'uploadUrl' => route('users.import.process'),
    ]);
}

public function exportAction()
{
    return Excel::download(new UsersExport, 'users.xlsx');
}
```

## Common Header Action Patterns

### Create New Record

```php
Action::make('create')
    ->label('Create User')
    ->color('primary')
    ->authorize(fn() => auth()->user()->can('create', User::class))

public function createAction()
{
    return redirect()->route('users.create');
}
```

### Data Export

```php
Action::make('export')
    ->label('Export CSV')
    ->color('secondary')
    ->authorize(fn() => auth()->user()->can('export-users'))

public function exportAction()
{
    $users = User::all();
    
    $filename = 'users_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    ];
    
    return response()->stream(function() use ($users) {
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Name', 'Email', 'Created At']);
        
        foreach ($users as $user) {
            fputcsv($output, [$user->name, $user->email, $user->created_at]);
        }
        
        fclose($output);
    }, 200, $headers);
}
```

### Data Import

```php
Action::make('import')
    ->label('Import Users')
    ->color('primary')
    ->authorize(fn() => auth()->user()->can('import-users'))

public function importAction()
{
    return Inertia::render('Users/Import', [
        'uploadUrl' => route('users.import.upload'),
        'templateUrl' => route('users.import.template'),
    ]);
}
```

### Bulk Operations on All Records

```php
Action::make('reset_passwords')
    ->label('Reset All Passwords')
    ->color('warning')
    ->requiresConfirmation(
        'Reset All Passwords',
        'This will reset passwords for all users and send them email notifications.'
    )
    ->authorize(fn() => auth()->user()->can('reset-all-passwords'))

public function resetPasswordsAction()
{
    $count = 0;
    
    User::chunk(100, function($users) use (&$count) {
        foreach ($users as $user) {
            $user->update(['password' => Hash::make(Str::random(12))]);
            $user->notify(new PasswordResetNotification());
            $count++;
        }
    });
    
    return back()->with('success', "Passwords reset for {$count} users");
}
```

## Advanced Header Action Examples

### Settings/Configuration

```php
Action::make('settings')
    ->label('Table Settings')
    ->color('gray')
    ->authorize(fn() => auth()->user()->can('configure-tables'))

public function settingsAction()
{
    return Inertia::render('Users/TableSettings', [
        'currentSettings' => auth()->user()->table_preferences ?? [],
        'availableColumns' => $this->getAvailableColumns(),
    ]);
}
```

### Generate Reports

```php
Action::make('generate_report')
    ->label('Generate Report')
    ->color('primary')
    ->authorize(fn() => auth()->user()->can('generate-reports'))

public function generateReportAction()
{
    // Queue a report generation job
    GenerateUserReport::dispatch(auth()->user());
    
    return back()->with('info', 'Report generation started. You will receive an email when complete.');
}
```

### Data Refresh/Sync

```php
Action::make('sync_external')
    ->label('Sync with External API')
    ->color('primary')
    ->authorize(fn() => auth()->user()->can('sync-external-data'))

public function syncExternalAction()
{
    try {
        $apiData = Http::get('https://api.example.com/users')->json();
        
        $synced = 0;
        foreach ($apiData as $userData) {
            User::updateOrCreate(
                ['external_id' => $userData['id']],
                [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'last_synced' => now(),
                ]
            );
            $synced++;
        }
        
        return back()->with('success', "Synced {$synced} records from external API");
        
    } catch (Exception $e) {
        return back()->with('error', 'Failed to sync with external API: ' . $e->getMessage());
    }
}
```

## Header Action Forms

### Actions with Input Forms

```php
Action::make('bulk_email')
    ->label('Send Bulk Email')
    ->color('primary')
    ->form([
        TextInput::make('subject')
            ->label('Email Subject')
            ->required(),
            
        Textarea::make('message')
            ->label('Email Message')
            ->required()
            ->rows(5),
            
        Select::make('recipient_filter')
            ->label('Send To')
            ->options([
                'all' => 'All Users',
                'active' => 'Active Users Only',
                'inactive' => 'Inactive Users Only',
            ])
            ->default('active'),
    ])
    ->authorize(fn() => auth()->user()->can('send-bulk-emails'))

public function bulkEmailAction()
{
    $data = request()->validate([
        'subject' => 'required|string|max:255',
        'message' => 'required|string',
        'recipient_filter' => 'required|in:all,active,inactive',
    ]);
    
    $query = User::query();
    
    if ($data['recipient_filter'] === 'active') {
        $query->where('is_active', true);
    } elseif ($data['recipient_filter'] === 'inactive') {
        $query->where('is_active', false);
    }
    
    $recipients = $query->get();
    
    foreach ($recipients as $recipient) {
        Mail::to($recipient->email)->send(
            new BulkEmail($data['subject'], $data['message'])
        );
    }
    
    return back()->with('success', "Email sent to {$recipients->count()} users");
}
```

## Conditional Header Actions

### Show Based on Permissions

```php
Action::make('admin_tools')
    ->label('Admin Tools')
    ->color('danger')
    ->visible(fn() => auth()->user()->isAdmin())
    ->authorize(fn() => auth()->user()->can('access-admin-tools'))
```

### Show Based on Data State

```php
Action::make('process_pending')
    ->label('Process Pending Orders')
    ->color('warning')
    ->visible(function() {
        return Order::where('status', 'pending')->count() > 0;
    })
    ->authorize(fn() => auth()->user()->can('process-orders'))

public function processPendingAction()
{
    $processed = Order::where('status', 'pending')
        ->update(['status' => 'processing', 'processed_at' => now()]);
    
    return back()->with('success', "Started processing {$processed} pending orders");
}
```

## Header Action Groups

Organize related header actions into dropdown menus:

```php
->headerActions([
    // Primary action
    Action::make('create')
        ->label('Create User')
        ->color('primary'),
        
    // Grouped actions
    ActionGroup::make([
        Action::make('import')
            ->label('Import CSV')
            ->color('secondary'),
            
        Action::make('export')
            ->label('Export All')
            ->color('secondary'),
            
        Action::make('export_filtered')
            ->label('Export Filtered')
            ->color('secondary'),
            
        Action::make('generate_report')
            ->label('Generate Report')
            ->color('primary'),
    ])->label('More Actions'),
])
```

## Integration with Table State

### Access Current Filters

```php
public function exportFilteredAction()
{
    $request = request();
    
    // Get current table state
    $search = $request->get('search');
    $sort = $request->get('sort');
    $filters = $request->get('filters', []);
    
    // Apply same filters as the table
    $query = User::query();
    
    if ($search) {
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }
    
    if (isset($filters['status'])) {
        $query->where('status', $filters['status']);
    }
    
    // Export the filtered data
    return Excel::download(
        new UsersExport($query->get()),
        'filtered_users.xlsx'
    );
}
```

### Refresh Table After Action

```php
public function refreshDataAction()
{
    // Perform some external sync or data refresh
    $this->syncExternalData();
    
    // Return back to refresh the table
    return back()->with('success', 'Data refreshed successfully');
}
```

## Testing Header Actions

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Tables\UserTable;
use Tests\TestCase;

class HeaderActionsTest extends TestCase
{
    public function test_create_action_redirects_to_create_page()
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        
        $table = UserTable::make();
        $response = $table->createAction();
        
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(route('users.create'), $response->getTargetUrl());
    }
    
    public function test_export_action_returns_csv_download()
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(5)->create();
        
        $this->actingAs($admin);
        
        $table = UserTable::make();
        $response = $table->exportAction();
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
    }
}
```

## Best Practices

### 1. Clear Action Naming

```php
// Good: Clear, action-oriented names
Action::make('create')->label('Create User')
Action::make('export')->label('Export All Data')
Action::make('import')->label('Import from CSV')

// Bad: Vague or unclear names
Action::make('new')->label('New')
Action::make('download')->label('Download')
```

### 2. Appropriate Colors

```php
// Primary actions (main table functions)
Action::make('create')->color('primary')

// Secondary actions (supporting functions)  
Action::make('export')->color('secondary')
Action::make('import')->color('secondary')

// Dangerous actions (system-wide changes)
Action::make('reset_all')->color('danger')
```

### 3. Proper Authorization

```php
Action::make('admin_function')
    ->authorize(fn() => auth()->user()->can('admin-functions'))
    ->visible(fn() => auth()->user()->isAdmin()) // Hide if not authorized
```

### 4. Helpful Confirmation Messages

```php
Action::make('reset_all_passwords')
    ->requiresConfirmation(
        'Reset All User Passwords',
        'This will reset passwords for ALL users in the system and send them email notifications. This action cannot be undone.'
    )
    ->confirmationButton('Yes, Reset All Passwords')
```

## Performance Considerations

### 1. Efficient Queries

```php
public function exportAction()
{
    // Efficient: Stream large datasets
    return response()->streamDownload(function() {
        $output = fopen('php://output', 'w');
        
        User::chunk(1000, function($users) use ($output) {
            foreach ($users as $user) {
                fputcsv($output, [$user->name, $user->email]);
            }
        });
        
        fclose($output);
    }, 'users.csv');
}
```

### 2. Queue Long-Running Operations

```php
public function generateReportAction()
{
    // Queue instead of running immediately
    GenerateUserReport::dispatch(auth()->user());
    
    return back()->with('info', 'Report generation started in background');
}
```

## Next Steps

- **[Custom Actions](/04-actions/05-custom-actions)** - Build your own action types
- **[Search & Filtering](/05-search-and-filtering)** - Advanced search capabilities
- **[React Integration](/07-react-integration)** - Frontend customization

## Header Action Reference

### Handler Method Signature

```php
public function {actionName}Action()
{
    // No parameters since header actions operate at table level
    // Return redirect, Inertia response, JSON, or file download
}
```

### Common Use Cases

| Use Case | Example | Description |
|----------|---------|-------------|
| **Create Records** | `->make('create')` | Navigate to creation form |
| **Data Export** | `->make('export')` | Download table data |
| **Data Import** | `->make('import')` | Upload and process files |
| **Reports** | `->make('report')` | Generate and download reports |
| **Settings** | `->make('settings')` | Configure table preferences |
| **Sync/Refresh** | `->make('sync')` | Update data from external sources |