---
title: Getting Started with Columns
description: Learn about the column system in Inertia Tables and how to configure different column types.
---

Columns are the fundamental building blocks of your data tables. They define how your data is displayed, formatted, and interacted with. Inertia Tables provides a flexible column system that allows you to customize every aspect of your table's appearance and behavior.

Column classes can be found in the `Egmond\InertiaTables\Columns` namespace. You can put them inside the `$table->columns()` method:

```php
use Egmond\InertiaTables\Table;

public function table(Table $table): Table
{
    return $table
        ->columns([
            // ...
        ]);
}
```

## Basic Column Structure

All columns in Inertia Tables extend from the `BaseColumn` class and follow a consistent API pattern:

```php
use Egmond\InertiaTables\Columns\TextColumn;

TextColumn::make('name')
```

## Available Column Types

Inertia Tables currently provides the following column types:

- [Text Column](/03-columns/02-text-column)

More column types are planned for future releases.

## Column Creation

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
    ->visible(true)              // Visible
    ->hidden(false)              // Visible
    ->visible(false)             // Always hidden
    ->hidden(true)               // Always hidden
    ->visible(fn() => auth()->user()->isAdmin()) // Conditional visibility
```

### Sorting

Enable sorting on columns:

```php
TextColumn::make('name')
    ->sortable()
```

### Searching

Make columns searchable:

```php
TextColumn::make('name')
    ->searchable()
```

## Working with Relationships

You can easily display relationship data using dot notation:

```php
TextColumn::make('author.name')
    ->label('Author')
    ->searchable()
    ->sortable()
```

## Method Chaining

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

## Conditional Column Behavior

Make columns behave differently based on conditions:

```php
TextColumn::make('name')
    ->searchable(fn () => auth()->user()->can('search-users'))
    ->sortable(fn () => request()->has('sort'))
```

## Next Steps

Now that you understand the basics of columns, explore specific column types and their features:

- **[Text Column](/columns/text-column)** - Learn about all TextColumn features and customization options
