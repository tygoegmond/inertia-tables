---
title: Getting Started with Columns
description: Learn about the column system in Inertia Tables and how to configure different column types.
---

## Overview

Columns are the fundamental building blocks of your data tables. They define how your data is displayed, formatted, and interacted with. Inertia Tables provides a flexible column system that allows you to customize every aspect of your table's appearance and behavior.

## Basic Column Structure

All columns in Inertia Tables extend from the `BaseColumn` class and follow a consistent API pattern:

```php
use Egmond\InertiaTables\Columns\TextColumn;

TextColumn::make('name')
    ->label('Full Name')
    ->sortable()
    ->searchable()
```

## Available Column Types

Inertia Tables currently provides the following column types:

### Text Column

The most common column type for displaying text data:

```php
TextColumn::make('name')
    ->label('Name')
    ->searchable()
    ->sortable()
```

More column types are planned for future releases, including:
- Image Column
- Icon Column  
- Color Column
- Boolean Column
- Date Column

## Column Creation

### Using the `make()` Method

All columns are created using the static `make()` method, which accepts the column name as its parameter:

```php
TextColumn::make('name')           // Simple column
TextColumn::make('user.name')      // Relationship column  
TextColumn::make('posts_count')    // Computed/aggregated column
```

The column name should correspond to:
- A column in your database table
- An accessor on your model
- A relationship using dot notation
- An aggregated field (like `posts_count`)

### Column Labels

By default, the column label is automatically generated from the column name. You can customize it:

```php
TextColumn::make('created_at')
    ->label('Date Created')
```

## Core Column Features

### Visibility

Control column visibility:

```php
TextColumn::make('email')
    ->visible(true)              // Always visible
    ->visible(false)             // Always hidden
    ->visible(fn() => auth()->user()->isAdmin()) // Conditional visibility
```

### Sorting

Enable sorting on columns:

```php
TextColumn::make('name')
    ->sortable()                 // Enable sorting
    ->sortable(false)            // Disable sorting
    ->defaultSort('asc')         // Set default sort direction
```

### Searching

Make columns searchable:

```php
TextColumn::make('name')
    ->searchable()               // Enable search on this column
    ->searchColumn('full_name')  // Search a different database column
```

### State Management

Each column maintains state that can be accessed and modified:

```php
TextColumn::make('status')
    ->formatStateUsing(fn ($state) => ucfirst($state))
```

## Working with Relationships

You can easily display relationship data using dot notation:

```php
TextColumn::make('author.name')
    ->label('Author')
    ->searchable()
    ->sortable()
```

Make sure to eager load the relationship in your table query:

```php
public function table(Table $table): Table
{
    return $table
        ->query(
            Post::query()->with('author')
        )
        ->columns([
            TextColumn::make('title'),
            TextColumn::make('author.name'),
        ]);
}
```

## Aggregated Columns

Display aggregated data using Laravel's query builder methods:

```php
// In your table
->query(
    User::query()->withCount('posts')
)
->columns([
    TextColumn::make('name'),
    TextColumn::make('posts_count')
        ->label('Total Posts'),
])
```

## Column Configuration Chain

Columns support method chaining for clean, readable configuration:

```php
TextColumn::make('email')
    ->label('Email Address')
    ->searchable()
    ->sortable()
    ->copyable()
    ->limit(30)
    ->wrap('truncate')
```

## Custom Column Formatting

You can customize how column data is formatted:

```php
TextColumn::make('price')
    ->formatStateUsing(fn ($state) => '$' . number_format($state, 2))
    ->label('Price')
```

## Column State

Each column has access to the full row data through closures:

```php
TextColumn::make('status')
    ->formatStateUsing(function ($state, $record) {
        return $record->is_active ? 'Active' : 'Inactive';
    })
```

## Conditional Column Behavior

Make columns behave differently based on conditions:

```php
TextColumn::make('name')
    ->searchable(fn () => auth()->user()->can('search-users'))
    ->sortable(fn () => request()->has('sort'))
```

## Column Collections

When defining multiple columns, you can organize them in arrays:

```php
public function table(Table $table): Table
{
    return $table->columns([
        // Basic info columns
        TextColumn::make('name')->searchable(),
        TextColumn::make('email')->searchable(), 
        
        // Metadata columns
        TextColumn::make('created_at')->sortable(),
        TextColumn::make('updated_at')->sortable(),
    ]);
}
```

## Next Steps

Now that you understand the basics of columns, explore specific column types and their features:

- **[Text Column](/03-columns/02-text-column)** - Learn about all TextColumn features and customization options
- **[Custom Columns](/03-columns/03-custom-columns)** - Create your own custom column types

## Column Reference

### Common Column Methods

| Method | Description | Example |
|--------|-------------|---------|
| `make(string $name)` | Create a new column | `TextColumn::make('name')` |
| `label(string $label)` | Set column label | `->label('Full Name')` |
| `sortable(bool $sortable = true)` | Enable/disable sorting | `->sortable()` |
| `searchable(bool $searchable = true)` | Enable/disable searching | `->searchable()` |
| `visible(bool\|Closure $visible = true)` | Control visibility | `->visible(false)` |
| `formatStateUsing(Closure $callback)` | Format the displayed value | `->formatStateUsing(fn($state) => ucfirst($state))` |

### Column Properties

Each column instance contains:
- `key`: The database column or accessor name
- `label`: The display label for the column
- `type`: The column type (e.g., 'text')
- `visible`: Whether the column should be displayed
- `sortable`: Whether the column can be sorted
- `searchable`: Whether the column can be searched
- `state`: Additional configuration options specific to the column type