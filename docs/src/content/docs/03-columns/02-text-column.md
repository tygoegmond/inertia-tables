---
title: Text Column
description: Comprehensive guide to using TextColumn for displaying and formatting text data in your tables.
---

## Overview

The `TextColumn` is the most versatile and commonly used column type in Inertia Tables. It's designed for displaying text-based data with extensive customization options for formatting, appearance, and user interaction.

## Basic Usage

Create a text column using the `make()` method:

```php
use Egmond\InertiaTables\Columns\TextColumn;

TextColumn::make('name')
    ->label('Full Name')
    ->searchable()
    ->sortable()
```

## Display Formatting

### Prefixes and Suffixes

Add text before or after the column value:

```php
TextColumn::make('price')
    ->prefix('$')
    ->suffix(' USD')
    ->label('Price')

// Will display: $29.99 USD
```

### Character Limiting

Limit the number of characters displayed:

```php
TextColumn::make('description')
    ->limit(50)
    ->label('Description')

// Long text will be truncated with "..."
```

### Text Wrapping

Control how text wraps within cells:

```php
TextColumn::make('title')
    ->wrap('truncate')    // Default: truncate with ellipsis
    ->wrap('break-words') // Break long words to prevent overflow
```

Available wrap options:
- `truncate` (default): Truncate with ellipsis
- `break-words`: Allow breaking within words

## Badge Display

Display values as styled badges:

```php
TextColumn::make('status')
    ->badge()                    // Enable badge display
    ->badgeVariant('success')    // Set static variant

// Or use dynamic variants based on value
TextColumn::make('status')
    ->badge()
    ->badgeVariant(function ($value, $record) {
        return match($value) {
            'active' => 'success',
            'inactive' => 'secondary', 
            'pending' => 'warning',
            'banned' => 'danger',
            default => 'default'
        };
    })
```

Available badge variants:
- `default` (gray)
- `primary` (blue)
- `success` (green)
- `warning` (yellow) 
- `danger` (red)
- `secondary` (dark gray)

## Copy to Clipboard

Enable users to copy column values:

```php
TextColumn::make('email')
    ->copyable()    // Adds a copy button
    ->label('Email Address')
```

When enabled, users can click an icon to copy the value to their clipboard.

## Custom Value Formatting

Transform how values are displayed using closures:

```php
TextColumn::make('created_at')
    ->formatStateUsing(fn ($state) => $state->format('M j, Y'))
    ->label('Created Date')

TextColumn::make('name')
    ->formatStateUsing(fn ($state) => strtoupper($state))
    ->label('Name (Uppercase)')

// Access the full record data
TextColumn::make('status')
    ->formatStateUsing(function ($state, $record) {
        if ($record['is_premium']) {
            return $state . ' (Premium)';
        }
        return $state;
    })
```

## Search and Sort Configuration

### Basic Search and Sort

```php
TextColumn::make('name')
    ->searchable()      // Enable search
    ->sortable()        // Enable sorting
```

### Custom Search Column

Search a different database column:

```php
TextColumn::make('display_name')
    ->searchable()
    ->searchColumn('full_name')  // Search the 'full_name' column instead
```

### Default Sorting

Set a default sort direction:

```php
TextColumn::make('created_at')
    ->sortable()
    ->defaultSort('desc')    // Sort descending by default
```

## Working with Relationships

Display relationship data using dot notation:

```php
TextColumn::make('author.name')
    ->label('Author')
    ->searchable()
    ->sortable()

TextColumn::make('category.title')
    ->label('Category')
    ->searchable()

// Multiple levels deep
TextColumn::make('author.profile.company')
    ->label('Author Company')
```

Remember to eager load relationships in your table query:

```php
public function table(Table $table): Table
{
    return $table
        ->query(
            Post::query()
                ->with(['author', 'category', 'author.profile'])
        )
        ->columns([
            TextColumn::make('title'),
            TextColumn::make('author.name'),
            TextColumn::make('category.title'),
        ]);
}
```

## Conditional Behavior

Make columns behave differently based on conditions:

```php
// Conditional visibility
TextColumn::make('secret_data')
    ->visible(fn () => auth()->user()->isAdmin())

// Conditional searchability  
TextColumn::make('email')
    ->searchable(fn () => auth()->user()->can('search-users'))

// Conditional formatting
TextColumn::make('status')
    ->formatStateUsing(function ($state, $record) {
        if ($record['is_featured']) {
            return '⭐ ' . $state;
        }
        return $state;
    })
```

## Advanced Examples

### Status Column with Icons and Colors

```php
TextColumn::make('status')
    ->badge()
    ->badgeVariant(function ($value) {
        return match($value) {
            'published' => 'success',
            'draft' => 'secondary',
            'pending' => 'warning', 
            'archived' => 'danger',
            default => 'default'
        };
    })
    ->formatStateUsing(function ($state) {
        $icons = [
            'published' => '✅',
            'draft' => '📝',
            'pending' => '⏳',
            'archived' => '📦',
        ];
        return ($icons[$state] ?? '') . ' ' . ucfirst($state);
    })
    ->searchable()
    ->sortable()
```

### Currency Column

```php
TextColumn::make('price')
    ->prefix('$')
    ->formatStateUsing(fn ($state) => number_format($state, 2))
    ->label('Price')
    ->sortable()
```

### Full Name from Separate Fields

```php
TextColumn::make('full_name')
    ->formatStateUsing(function ($state, $record) {
        return trim($record['first_name'] . ' ' . $record['last_name']);
    })
    ->label('Full Name')
    ->searchable()
    ->searchColumn('first_name,last_name')  // Search both columns
```

### Time Ago Display

```php
TextColumn::make('created_at')
    ->formatStateUsing(fn ($state) => $state->diffForHumans())
    ->label('Created')
    ->sortable()
```

## Method Reference

### Display Methods

| Method | Description | Example |
|--------|-------------|---------|
| `prefix(string $prefix)` | Add text before the value | `->prefix('$')` |
| `suffix(string $suffix)` | Add text after the value | `->suffix(' USD')` |
| `limit(int $limit)` | Limit character count | `->limit(50)` |
| `wrap(string $wrap)` | Set text wrapping behavior | `->wrap('break-words')` |

### Badge Methods

| Method | Description | Example |
|--------|-------------|---------|
| `badge(bool $badge = true)` | Enable badge display | `->badge()` |
| `badgeVariant(string\|Closure $variant)` | Set badge color variant | `->badgeVariant('success')` |

### Interaction Methods

| Method | Description | Example |
|--------|-------------|---------|
| `copyable(bool $copyable = true)` | Enable copy to clipboard | `->copyable()` |

### Inherited Methods

TextColumn inherits all base column methods:

| Method | Description | Example |
|--------|-------------|---------|
| `label(string $label)` | Set column label | `->label('Full Name')` |
| `searchable(bool $searchable = true)` | Enable searching | `->searchable()` |
| `sortable(bool $sortable = true)` | Enable sorting | `->sortable()` |
| `visible(bool\|Closure $visible = true)` | Control visibility | `->visible(false)` |
| `formatStateUsing(Closure $callback)` | Custom value formatting | `->formatStateUsing(fn($s) => ucfirst($s))` |

## Frontend Display

The TextColumn renders different display modes based on configuration:

### Standard Text
```php
TextColumn::make('name')  // Displays as plain text
```

### Badge Display  
```php
TextColumn::make('status')->badge()  // Displays as a styled badge
```

### Copyable Text
```php
TextColumn::make('email')->copyable()  // Displays with copy button
```

### Truncated Text
```php
TextColumn::make('description')->limit(50)  // Shows truncated text with tooltip on hover
```

## Performance Considerations

- Use `limit()` for long text content to improve rendering performance
- When using relationship columns, always eager load the relationships
- Complex `formatStateUsing` closures can impact performance with large datasets
- Consider database-level formatting for computationally expensive operations

## Next Steps

- **[Custom Columns](/03-columns/03-custom-columns)** - Learn how to create your own column types
- **[Actions](/04-actions/01-getting-started)** - Add interactive actions to your tables
- **[Search & Filtering](/05-search-and-filtering)** - Advanced search functionality