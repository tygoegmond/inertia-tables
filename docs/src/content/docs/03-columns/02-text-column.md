---
title: Text Column
description: Comprehensive guide to using TextColumn for displaying and formatting text data in your tables.
---

Text columns display simple text from your data source. They can be formatted, styled, and configured for various interactions like searching, sorting, and copying.

```php
use Egmond\InertiaTables\Columns\TextColumn;

TextColumn::make('name')
```

## Adding a prefix or suffix

Add text before or after the column value:

```php
TextColumn::make('price')
    ->prefix('$')
    ->suffix(' USD')

// Will display: $29.99 USD
```

## Limiting text length

Limit the number of characters displayed:

```php
TextColumn::make('description')
    ->limit(50)

// Long text will be truncated with "..."
```

## Wrapping content

If you'd like your text to wrap if it's too long, you can use the `wrap()` method:

```php
TextColumn::make('title')
    ->wrap()
```

## Displaying as a "badge"

Display values as styled badges:

```php
TextColumn::make('status')
    ->badge()
    ->badgeVariant('secondary')
```

Available badge variants:
- `default`
- `secondary`
- `destructive`
- `outline`

## Allowing text to be copied to the clipboard

Enable users to copy column values:

```php
TextColumn::make('email')
    ->copyable()
```

## Next Steps

- **[Actions](/04-actions/01-getting-started)** - Add interactive actions to your tables
