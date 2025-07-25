---
title: Custom Columns
description: Learn how to create custom column types to extend Inertia Tables with your own display logic and formatting.
---

## Overview

While Inertia Tables provides a powerful `TextColumn` out of the box, you may need to create custom column types for specialized display requirements. Custom columns allow you to encapsulate complex formatting logic, create reusable components, and extend the functionality of your tables.

## Creating a Custom Column

### Step 1: Extend BaseColumn

Create a new column class that extends `BaseColumn`:

```php
<?php

namespace App\Tables\Columns;

use Egmond\InertiaTables\Columns\BaseColumn;

class StatusColumn extends BaseColumn
{
    protected string $type = 'status';
    
    protected array $statusColors = [
        'active' => 'success',
        'inactive' => 'secondary',
        'pending' => 'warning',
        'suspended' => 'danger',
    ];
    
    public function statusColors(array $colors): static
    {
        $this->statusColors = $colors;
        return $this;
    }
    
    public function formatValue(mixed $value, array $record): mixed
    {
        // Let parent handle basic formatting first
        $value = parent::formatValue($value, $record);
        
        // Apply custom formatting
        return [
            'value' => ucfirst($value),
            'color' => $this->statusColors[$value] ?? 'default',
            'icon' => $this->getStatusIcon($value),
        ];
    }
    
    protected function getStatusIcon(string $status): string
    {
        return match($status) {
            'active' => '✅',
            'inactive' => '⚫',
            'pending' => '⏳',
            'suspended' => '❌',
            default => '❓',
        };
    }
    
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['statusColors'] = $this->statusColors;
        
        return $data;
    }
}
```

### Step 2: Use Your Custom Column

```php
use App\Tables\Columns\StatusColumn;

public function table(Table $table): Table
{
    return $table->columns([
        TextColumn::make('name'),
        StatusColumn::make('status')
            ->label('Account Status')
            ->statusColors([
                'active' => 'success',
                'trial' => 'primary',
                'expired' => 'danger',
            ])
            ->sortable(),
    ]);
}
```

## Advanced Custom Column Examples

### Image Column

Create a column for displaying images:

```php
<?php

namespace App\Tables\Columns;

use Egmond\InertiaTables\Columns\BaseColumn;

class ImageColumn extends BaseColumn
{
    protected string $type = 'image';
    protected int $size = 40;
    protected bool $rounded = true;
    protected ?string $fallback = null;
    
    public function size(int $size): static
    {
        $this->size = $size;
        return $this;
    }
    
    public function rounded(bool $rounded = true): static
    {
        $this->rounded = $rounded;
        return $this;
    }
    
    public function fallback(string $fallback): static
    {
        $this->fallback = $fallback;
        return $this;
    }
    
    public function formatValue(mixed $value, array $record): mixed
    {
        // Handle null values
        if (!$value) {
            return $this->fallback;
        }
        
        // Ensure full URL for images
        if (!str_starts_with($value, 'http')) {
            $value = asset($value);
        }
        
        return $value;
    }
    
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['size'] = $this->size;
        $data['rounded'] = $this->rounded;
        $data['fallback'] = $this->fallback;
        
        return $data;
    }
}
```

Usage:

```php
ImageColumn::make('avatar')
    ->label('Avatar')
    ->size(50)
    ->rounded()
    ->fallback('/images/default-avatar.png')
```

### Currency Column

Create a specialized column for currency values:

```php
<?php

namespace App\Tables\Columns;

use Egmond\InertiaTables\Columns\BaseColumn;

class CurrencyColumn extends BaseColumn
{
    protected string $type = 'currency';
    protected string $currency = 'USD';
    protected string $locale = 'en_US';
    protected int $decimals = 2;
    
    public function currency(string $currency): static
    {
        $this->currency = $currency;
        return $this;
    }
    
    public function locale(string $locale): static
    {
        $this->locale = $locale;
        return $this;
    }
    
    public function decimals(int $decimals): static
    {
        $this->decimals = $decimals;
        return $this;
    }
    
    public function formatValue(mixed $value, array $record): mixed
    {
        if ($value === null) {
            return null;
        }
        
        $formatter = new \NumberFormatter($this->locale, \NumberFormatter::CURRENCY);
        $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, $this->decimals);
        
        return $formatter->formatCurrency((float) $value, $this->currency);
    }
    
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['currency'] = $this->currency;
        $data['locale'] = $this->locale;
        $data['decimals'] = $this->decimals;
        
        return $data;
    }
}
```

Usage:

```php
CurrencyColumn::make('price')
    ->currency('EUR')
    ->locale('de_DE')
    ->decimals(2)
    ->sortable()
```

### Progress Column

Create a column that displays progress bars:

```php
<?php

namespace App\Tables\Columns;

use Egmond\InertiaTables\Columns\BaseColumn;

class ProgressColumn extends BaseColumn
{
    protected string $type = 'progress';
    protected int $max = 100;
    protected string $color = 'primary';
    protected bool $showLabel = true;
    
    public function max(int $max): static
    {
        $this->max = $max;
        return $this;
    }
    
    public function color(string $color): static
    {
        $this->color = $color;
        return $this;
    }
    
    public function showLabel(bool $show = true): static
    {
        $this->showLabel = $show;
        return $this;
    }
    
    public function formatValue(mixed $value, array $record): mixed
    {
        $value = (int) $value;
        $percentage = min(100, max(0, ($value / $this->max) * 100));
        
        return [
            'value' => $value,
            'percentage' => round($percentage, 1),
            'max' => $this->max,
        ];
    }
    
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['max'] = $this->max;
        $data['color'] = $this->color;
        $data['showLabel'] = $this->showLabel;
        
        return $data;
    }
}
```

Usage:

```php
ProgressColumn::make('completion_rate')
    ->label('Progress')
    ->max(100)
    ->color('success')
    ->showLabel()
```

## Frontend Integration

### React Component for Custom Columns

When you create custom column types, you'll need corresponding React components to render them. Create a custom column renderer:

```tsx
// resources/js/Components/Table/CustomColumns.tsx
import React from 'react';

interface StatusColumnProps {
  value: {
    value: string;
    color: string;
    icon: string;
  };
}

export function StatusColumn({ value }: StatusColumnProps) {
  const colorClasses = {
    success: 'bg-green-100 text-green-800',
    warning: 'bg-yellow-100 text-yellow-800',
    danger: 'bg-red-100 text-red-800',
    secondary: 'bg-gray-100 text-gray-800',
    default: 'bg-gray-100 text-gray-800',
  };

  return (
    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${colorClasses[value.color] || colorClasses.default}`}>
      <span className="mr-1">{value.icon}</span>
      {value.value}
    </span>
  );
}

interface ImageColumnProps {
  value: string;
  config: {
    size: number;
    rounded: boolean;
    fallback?: string;
  };
}

export function ImageColumn({ value, config }: ImageColumnProps) {
  return (
    <img
      src={value || config.fallback}
      alt=""
      className={`${config.rounded ? 'rounded-full' : 'rounded'}`}
      style={{ width: config.size, height: config.size }}
      onError={(e) => {
        if (config.fallback) {
          e.currentTarget.src = config.fallback;
        }
      }}
    />
  );
}
```

### Register Custom Column Renderers

Register your custom renderers with the table component:

```tsx
import { InertiaTable } from '@tygoegmond/inertia-tables-react';
import { StatusColumn, ImageColumn } from './Components/Table/CustomColumns';

const customColumnRenderers = {
  status: StatusColumn,
  image: ImageColumn,
};

export default function MyTable({ tableData }) {
  return (
    <InertiaTable
      state={tableData}
      customColumnRenderers={customColumnRenderers}
    />
  );
}
```

## Column Inheritance Patterns

### Shared Functionality

Create base classes for common functionality:

```php
<?php

namespace App\Tables\Columns;

use Egmond\InertiaTables\Columns\BaseColumn;

abstract class ColoredColumn extends BaseColumn
{
    protected array $colorMap = [];
    
    public function colors(array $colorMap): static
    {
        $this->colorMap = $colorMap;
        return $this;
    }
    
    protected function getColor(string $value): string
    {
        return $this->colorMap[$value] ?? 'default';
    }
    
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['colorMap'] = $this->colorMap;
        
        return $data;
    }
}
```

Then extend it:

```php
class PriorityColumn extends ColoredColumn
{
    protected string $type = 'priority';
    
    protected array $colorMap = [
        'high' => 'danger',
        'medium' => 'warning', 
        'low' => 'success',
    ];
    
    // Additional priority-specific methods...
}
```

## Testing Custom Columns

Create tests for your custom columns:

```php
<?php

namespace Tests\Unit\Tables\Columns;

use App\Tables\Columns\StatusColumn;
use PHPUnit\Framework\TestCase;

class StatusColumnTest extends TestCase
{
    public function test_formats_status_value()
    {
        $column = StatusColumn::make('status');
        
        $result = $column->formatValue('active', ['id' => 1]);
        
        $this->assertEquals('Active', $result['value']);
        $this->assertEquals('success', $result['color']);
        $this->assertEquals('✅', $result['icon']);
    }
    
    public function test_custom_status_colors()
    {
        $column = StatusColumn::make('status')
            ->statusColors(['active' => 'primary']);
        
        $result = $column->formatValue('active', ['id' => 1]);
        
        $this->assertEquals('primary', $result['color']);
    }
}
```

## Best Practices

### 1. Keep Columns Focused
Each custom column should have a single, clear purpose:

```php
// Good: Focused on status display
class StatusColumn extends BaseColumn { /* ... */ }

// Bad: Trying to do too many things
class StatusWithActionsAndRelationsColumn extends BaseColumn { /* ... */ }
```

### 2. Make Columns Configurable
Allow customization through method chaining:

```php
StatusColumn::make('status')
    ->colors(['active' => 'success'])
    ->showIcon(false)
    ->format('uppercase')
```

### 3. Handle Edge Cases
Always handle null values and unexpected data:

```php
public function formatValue(mixed $value, array $record): mixed
{
    if ($value === null) {
        return $this->defaultValue ?? 'N/A';
    }
    
    // Handle your formatting logic
}
```

### 4. Document Your Columns
Provide clear documentation and examples:

```php
/**
 * A column for displaying status values with colors and icons.
 * 
 * @example
 * StatusColumn::make('status')
 *     ->colors(['active' => 'success', 'inactive' => 'danger'])
 *     ->showIcon()
 */
class StatusColumn extends BaseColumn
{
    // Implementation...
}
```

## Column Package Structure

For reusable columns, consider creating a package structure:

```
src/
├── Columns/
│   ├── BaseColoredColumn.php
│   ├── StatusColumn.php
│   ├── CurrencyColumn.php
│   └── ImageColumn.php
├── React/
│   ├── StatusColumn.tsx
│   ├── CurrencyColumn.tsx
│   └── ImageColumn.tsx
└── ServiceProvider.php
```

## Next Steps

Now that you understand custom columns, explore other table features:

- **[Actions](/04-actions/01-getting-started)** - Add interactive buttons and operations
- **[Search & Filtering](/05-search-and-filtering)** - Advanced search capabilities  
- **[React Integration](/07-react-integration)** - Frontend customization and styling