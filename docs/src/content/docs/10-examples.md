---
title: Examples
description: Real-world examples and patterns for implementing Inertia Tables in various scenarios.
---

## Overview

This page provides practical, real-world examples of implementing Inertia Tables in different scenarios. Each example includes both the PHP backend table configuration and the React frontend implementation.

## User Management Table

A comprehensive user management table with full CRUD operations, search, and status management.

### Backend Implementation

```php
<?php

namespace App\Tables;

use App\Models\User;
use Egmond\InertiaTables\Actions\Action;
use Egmond\InertiaTables\Actions\BulkAction;
use Egmond\InertiaTables\Columns\TextColumn;
use Egmond\InertiaTables\Concerns\InteractsWithTable;
use Egmond\InertiaTables\Contracts\HasTable;
use Egmond\InertiaTables\Table;

class UserManagementTable implements HasTable
{
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table->as('users')
            ->query(
                User::query()
                    ->with(['roles', 'profile'])
                    ->withCount(['posts', 'comments'])
            )
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                    
                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                    
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->badgeVariant(function ($value) {
                        return match(strtolower($value)) {
                            'admin' => 'danger',
                            'moderator' => 'warning',
                            'user' => 'success',
                            default => 'secondary'
                        };
                    }),
                    
                TextColumn::make('posts_count')
                    ->label('Posts')
                    ->sortable(),
                    
                TextColumn::make('status')
                    ->badge()
                    ->badgeVariant(function ($value) {
                        return match($value) {
                            'active' => 'success',
                            'inactive' => 'secondary',
                            'suspended' => 'danger',
                            default => 'secondary'
                        };
                    })
                    ->formatStateUsing(fn($state) => ucfirst($state)),
                    
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state->format('M j, Y')),
            ])
            ->actions([
                Action::make('view')
                    ->label('View Profile')
                    ->color('secondary'),
                    
                Action::make('edit')
                    ->label('Edit')
                    ->color('primary')
                    ->authorize(fn($record) => auth()->user()->can('update', $record)),
                    
                Action::make('toggle_status')
                    ->label(fn($record) => $record->status === 'active' ? 'Suspend' : 'Activate')
                    ->color(fn($record) => $record->status === 'active' ? 'warning' : 'success')
                    ->requiresConfirmation(
                        fn($record) => $record->status === 'active' ? 'Suspend User' : 'Activate User',
                        fn($record) => "Are you sure you want to {$record->status === 'active' ? 'suspend' : 'activate'} {$record->name}?"
                    )
                    ->authorize(fn($record) => auth()->user()->can('manage-status', $record)),
                    
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
                    ->authorize(fn() => auth()->user()->can('bulk-manage-users')),
                    
                BulkAction::make('suspend')
                    ->label('Suspend Selected')
                    ->color('warning')
                    ->requiresConfirmation('Suspend Users', 'Are you sure you want to suspend the selected users?')
                    ->authorize(fn() => auth()->user()->can('bulk-manage-users')),
                    
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
                    
                Action::make('import')
                    ->label('Import Users')
                    ->color('secondary'),
            ])
            ->searchable()
            ->defaultSort('created_at', 'desc')
            ->paginate(20);
    }

    // Action Handlers
    public function viewAction($record)
    {
        return redirect()->route('users.show', $record);
    }

    public function editAction($record)
    {
        return redirect()->route('users.edit', $record);
    }

    public function toggleStatusAction($record)
    {
        $newStatus = $record->status === 'active' ? 'inactive' : 'active';
        $record->update(['status' => $newStatus]);
        
        return back()->with('success', "User {$newStatus === 'active' ? 'activated' : 'suspended'} successfully");
    }

    public function deleteAction($record)
    {
        $record->delete();
        return back()->with('success', 'User deleted successfully');
    }

    // Bulk Action Handlers
    public function activateBulkAction($records)
    {
        $count = User::whereIn('id', collect($records)->pluck('id'))
            ->update(['status' => 'active']);
            
        return back()->with('success', "{$count} users activated");
    }

    public function suspendBulkAction($records)
    {
        $count = User::whereIn('id', collect($records)->pluck('id'))
            ->update(['status' => 'inactive']);
            
        return back()->with('success', "{$count} users suspended");
    }

    public function deleteBulkAction($records)
    {
        $count = User::whereIn('id', collect($records)->pluck('id'))->delete();
        return back()->with('success', "{$count} users deleted");
    }

    // Header Action Handlers
    public function createAction()
    {
        return redirect()->route('users.create');
    }

    public function exportAction()
    {
        return Excel::download(new UsersExport, 'users.csv');
    }

    public function importAction()
    {
        return Inertia::render('Users/Import');
    }
}
```

### Frontend Implementation

```tsx
import React from 'react';
import { InertiaTable } from '@tygoegmond/inertia-tables-react';
import { Head } from '@inertiajs/react';
import { toast } from 'sonner';

interface User {
  id: number;
  name: string;
  email: string;
  status: 'active' | 'inactive' | 'suspended';
  created_at: string;
  roles: Array<{ name: string }>;
  posts_count: number;
}

interface UsersIndexProps {
  users: TableResult<User>;
}

export default function UsersIndex({ users }: UsersIndexProps) {
  return (
    <>
      <Head title="User Management" />
      
      <div className="p-6">
        <div className="mb-6">
          <h1 className="text-3xl font-bold text-gray-900">User Management</h1>
          <p className="mt-2 text-gray-600">
            Manage user accounts, roles, and permissions
          </p>
        </div>

        <div className="bg-white rounded-lg shadow">
          <InertiaTable
            state={users}
            onSuccess={(message) => toast.success(message)}
            onError={(error) => toast.error(error)}
            className="rounded-lg overflow-hidden"
          />
        </div>
      </div>
    </>
  );
}
```

## E-commerce Order Management

A complex table for managing e-commerce orders with status tracking, filtering, and order processing actions.

### Backend Implementation

```php
<?php

namespace App\Tables;

use App\Models\Order;
use Egmond\InertiaTables\Actions\Action;
use Egmond\InertiaTables\Actions\BulkAction;
use Egmond\InertiaTables\Columns\TextColumn;
use Egmond\InertiaTables\Concerns\InteractsWithTable;
use Egmond\InertiaTables\Contracts\HasTable;
use Egmond\InertiaTables\Table;

class OrderManagementTable implements HasTable
{
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table->as('orders')
            ->query($this->buildQuery())
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->copyable(),
                    
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),
                    
                TextColumn::make('total')
                    ->prefix('$')
                    ->formatStateUsing(fn($state) => number_format($state, 2))
                    ->sortable(),
                    
                TextColumn::make('status')
                    ->badge()
                    ->badgeVariant(function ($value) {
                        return match($value) {
                            'pending' => 'warning',
                            'processing' => 'primary',
                            'shipped' => 'info',
                            'delivered' => 'success',
                            'cancelled' => 'danger',
                            'refunded' => 'secondary',
                            default => 'secondary'
                        };
                    })
                    ->sortable(),
                    
                TextColumn::make('items_count')
                    ->label('Items')
                    ->sortable(),
                    
                TextColumn::make('shipping_method')
                    ->label('Shipping'),
                    
                TextColumn::make('created_at')
                    ->label('Order Date')
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state->format('M j, Y g:i A')),
            ])
            ->actions([
                Action::make('view')
                    ->label('View Details')
                    ->color('secondary'),
                    
                Action::make('process')
                    ->label('Process Order')
                    ->color('primary')
                    ->visible(fn($record) => $record->status === 'pending')
                    ->authorize(fn($record) => auth()->user()->can('process-orders')),
                    
                Action::make('ship')
                    ->label('Mark as Shipped')
                    ->color('info')
                    ->visible(fn($record) => $record->status === 'processing')
                    ->requiresConfirmation('Ship Order', 'Mark this order as shipped?'),
                    
                Action::make('refund')
                    ->label('Process Refund')
                    ->color('warning')
                    ->visible(fn($record) => in_array($record->status, ['delivered', 'shipped']))
                    ->requiresConfirmation('Process Refund', 'This will initiate a refund for this order.'),
                    
                Action::make('cancel')
                    ->label('Cancel Order')
                    ->color('danger')
                    ->visible(fn($record) => in_array($record->status, ['pending', 'processing']))
                    ->requiresConfirmation('Cancel Order', 'Are you sure you want to cancel this order?'),
            ])
            ->bulkActions([
                BulkAction::make('bulk_process')
                    ->label('Process Selected')
                    ->color('primary')
                    ->authorize(fn() => auth()->user()->can('bulk-process-orders')),
                    
                BulkAction::make('bulk_ship')
                    ->label('Mark as Shipped')
                    ->color('info')
                    ->requiresConfirmation('Ship Orders', 'Mark selected orders as shipped?')
                    ->authorize(fn() => auth()->user()->can('bulk-ship-orders')),
                    
                BulkAction::make('export_selected')
                    ->label('Export Selected')
                    ->color('secondary')
                    ->authorize(fn() => auth()->user()->can('export-orders')),
            ])
            ->headerActions([
                Action::make('create_manual')
                    ->label('Create Manual Order')
                    ->color('primary'),
                    
                Action::make('export_all')
                    ->label('Export All Orders')
                    ->color('secondary'),
                    
                Action::make('import_tracking')
                    ->label('Import Tracking Numbers')
                    ->color('secondary'),
            ])
            ->searchable()
            ->defaultSort('created_at', 'desc')
            ->paginate(25);
    }

    protected function buildQuery()
    {
        $query = Order::query()
            ->with(['customer', 'items', 'shipping_address'])
            ->withCount('items');

        // Apply filters
        if (request('status')) {
            $query->where('status', request('status'));
        }

        if (request('date_from')) {
            $query->whereDate('created_at', '>=', request('date_from'));
        }

        if (request('date_to')) {
            $query->whereDate('created_at', '<=', request('date_to'));
        }

        if (request('min_total')) {
            $query->where('total', '>=', request('min_total'));
        }

        return $query;
    }

    // Action implementations...
    public function processAction($record)
    {
        $record->update(['status' => 'processing']);
        
        // Send notification to customer
        $record->customer->notify(new OrderProcessingNotification($record));
        
        return back()->with('success', 'Order marked as processing');
    }

    public function shipAction($record)
    {
        $record->update([
            'status' => 'shipped',
            'shipped_at' => now(),
        ]);
        
        return back()->with('success', 'Order marked as shipped');
    }
}
```

### Frontend with Filters

```tsx
import React, { useState } from 'react';
import { InertiaTable } from '@tygoegmond/inertia-tables-react';
import { router } from '@inertiajs/react';

interface OrderFilters {
  status?: string;
  date_from?: string;
  date_to?: string;
  min_total?: string;
}

export default function OrdersIndex({ orders, filters }: { orders: any, filters: OrderFilters }) {
  const [localFilters, setLocalFilters] = useState<OrderFilters>(filters);

  const applyFilters = () => {
    router.get(route('orders.index'), localFilters, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  const clearFilters = () => {
    setLocalFilters({});
    router.get(route('orders.index'), {}, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  return (
    <div className="p-6 space-y-6">
      <div className="flex justify-between items-center">
        <h1 className="text-3xl font-bold">Order Management</h1>
        <div className="flex space-x-2">
          <button className="px-4 py-2 bg-blue-600 text-white rounded">
            Create Manual Order
          </button>
        </div>
      </div>

      {/* Advanced Filters */}
      <div className="bg-white p-6 rounded-lg shadow">
        <h3 className="text-lg font-medium mb-4">Filters</h3>
        <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700">Status</label>
            <select
              value={localFilters.status || ''}
              onChange={(e) => setLocalFilters({...localFilters, status: e.target.value})}
              className="mt-1 block w-full rounded-md border-gray-300"
            >
              <option value="">All Statuses</option>
              <option value="pending">Pending</option>
              <option value="processing">Processing</option>
              <option value="shipped">Shipped</option>
              <option value="delivered">Delivered</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700">From Date</label>
            <input
              type="date"
              value={localFilters.date_from || ''}
              onChange={(e) => setLocalFilters({...localFilters, date_from: e.target.value})}
              className="mt-1 block w-full rounded-md border-gray-300"
            />
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700">To Date</label>
            <input
              type="date"
              value={localFilters.date_to || ''}
              onChange={(e) => setLocalFilters({...localFilters, date_to: e.target.value})}
              className="mt-1 block w-full rounded-md border-gray-300"
            />
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700">Min Total</label>
            <input
              type="number"
              value={localFilters.min_total || ''}
              onChange={(e) => setLocalFilters({...localFilters, min_total: e.target.value})}
              placeholder="0.00"
              className="mt-1 block w-full rounded-md border-gray-300"
            />
          </div>
          
          <div className="flex items-end space-x-2">
            <button
              onClick={applyFilters}
              className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
            >
              Apply
            </button>
            <button
              onClick={clearFilters}
              className="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
            >
              Clear
            </button>
          </div>
        </div>
      </div>

      {/* Orders Table */}
      <div className="bg-white rounded-lg shadow">
        <InertiaTable
          state={orders}
          onSuccess={(message) => toast.success(message)}
          onError={(error) => toast.error(error)}
        />
      </div>
    </div>
  );
}
```

## Blog Post Management

A content management table for blog posts with draft/published states, categories, and content management features.

### Backend Implementation

```php
<?php

namespace App\Tables;

use App\Models\Post;
use Egmond\InertiaTables\Actions\Action;
use Egmond\InertiaTables\Actions\BulkAction;
use Egmond\InertiaTables\Columns\TextColumn;
use Egmond\InertiaTables\Concerns\InteractsWithTable;
use Egmond\InertiaTables\Contracts\HasTable;
use Egmond\InertiaTables\Table;

class BlogPostTable implements HasTable
{
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table->as('posts')
            ->query(
                Post::query()
                    ->with(['author', 'category', 'tags'])
                    ->withCount(['comments', 'views'])
            )
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                    
                TextColumn::make('author.name')
                    ->label('Author')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->badgeVariant('primary'),
                    
                TextColumn::make('status')
                    ->badge()
                    ->badgeVariant(function ($value) {
                        return match($value) {
                            'published' => 'success',
                            'draft' => 'warning',
                            'scheduled' => 'info',
                            'archived' => 'secondary',
                            default => 'secondary'
                        };
                    })
                    ->sortable(),
                    
                TextColumn::make('comments_count')
                    ->label('Comments')
                    ->sortable(),
                    
                TextColumn::make('views_count')
                    ->label('Views')
                    ->sortable(),
                    
                TextColumn::make('published_at')
                    ->label('Published')
                    ->formatStateUsing(function ($state, $record) {
                        if (!$state) return 'Not published';
                        return $state->format('M j, Y');
                    })
                    ->sortable(),
            ])
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->color('secondary')
                    ->visible(fn($record) => $record->status === 'published'),
                    
                Action::make('edit')
                    ->label('Edit')
                    ->color('primary')
                    ->authorize(fn($record) => auth()->user()->can('update', $record)),
                    
                Action::make('publish')
                    ->label('Publish')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'draft')
                    ->authorize(fn($record) => auth()->user()->can('publish', $record)),
                    
                Action::make('unpublish')
                    ->label('Unpublish')
                    ->color('warning')
                    ->visible(fn($record) => $record->status === 'published')
                    ->requiresConfirmation('Unpublish Post', 'This will make the post unavailable to readers.'),
                    
                Action::make('duplicate')
                    ->label('Duplicate')
                    ->color('secondary'),
                    
                Action::make('delete')
                    ->label('Delete')
                    ->color('danger')
                    ->requiresConfirmation('Delete Post', 'This action cannot be undone.')
                    ->authorize(fn($record) => auth()->user()->can('delete', $record)),
            ])
            ->bulkActions([
                BulkAction::make('publish')
                    ->label('Publish Selected')
                    ->color('success')
                    ->authorize(fn() => auth()->user()->can('bulk-publish')),
                    
                BulkAction::make('unpublish')
                    ->label('Unpublish Selected')
                    ->color('warning')
                    ->requiresConfirmation('Unpublish Posts', 'This will make selected posts unavailable.')
                    ->authorize(fn() => auth()->user()->can('bulk-publish')),
                    
                BulkAction::make('change_category')
                    ->label('Change Category')
                    ->color('primary')
                    ->authorize(fn() => auth()->user()->can('bulk-edit')),
                    
                BulkAction::make('delete')
                    ->label('Delete Selected')
                    ->color('danger')
                    ->requiresConfirmation('Delete Posts', 'This will permanently delete all selected posts.')
                    ->authorize(fn() => auth()->user()->can('bulk-delete')),
            ])
            ->headerActions([
                Action::make('create')
                    ->label('New Post')
                    ->color('primary'),
                    
                Action::make('import')
                    ->label('Import Posts')
                    ->color('secondary'),
                    
                Action::make('export')
                    ->label('Export Data')
                    ->color('secondary'),
            ])
            ->searchable()
            ->defaultSort('updated_at', 'desc')
            ->paginate(15);
    }

    // Action implementations
    public function publishAction($record)
    {
        $record->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
        
        return back()->with('success', 'Post published successfully');
    }

    public function duplicateAction($record)
    {
        $duplicate = $record->replicate();
        $duplicate->title = $record->title . ' (Copy)';
        $duplicate->slug = $record->slug . '-copy';
        $duplicate->status = 'draft';
        $duplicate->published_at = null;
        $duplicate->save();
        
        return redirect()->route('posts.edit', $duplicate)
            ->with('success', 'Post duplicated successfully');
    }
}
```

## File Management Table

A file manager table with upload, download, and file organization features.

### Backend Implementation

```php
<?php

namespace App\Tables;

use App\Models\File;
use Egmond\InertiaTables\Actions\Action;
use Egmond\InertiaTables\Actions\BulkAction;
use Egmond\InertiaTables\Columns\TextColumn;
use Egmond\InertiaTables\Concerns\InteractsWithTable;
use Egmond\InertiaTables\Contracts\HasTable;
use Egmond\InertiaTables\Table;

class FileManagementTable implements HasTable
{
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table->as('files')
            ->query(File::query()->with(['folder', 'user']))
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('type')
                    ->badge()
                    ->badgeVariant(function ($value) {
                        return match(true) {
                            str_contains($value, 'image') => 'success',
                            str_contains($value, 'video') => 'primary',
                            str_contains($value, 'audio') => 'info',
                            str_contains($value, 'pdf') => 'danger',
                            default => 'secondary'
                        };
                    }),
                    
                TextColumn::make('size')
                    ->formatStateUsing(fn($state) => $this->formatFileSize($state))
                    ->sortable(),
                    
                TextColumn::make('folder.name')
                    ->label('Folder'),
                    
                TextColumn::make('user.name')
                    ->label('Uploaded By'),
                    
                TextColumn::make('created_at')
                    ->label('Upload Date')
                    ->formatStateUsing(fn($state) => $state->diffForHumans())
                    ->sortable(),
            ])
            ->actions([
                Action::make('download')
                    ->label('Download')
                    ->color('primary'),
                    
                Action::make('preview')
                    ->label('Preview')
                    ->color('secondary')
                    ->visible(fn($record) => $this->isPreviewable($record->type)),
                    
                Action::make('move')
                    ->label('Move')
                    ->color('secondary'),
                    
                Action::make('rename')
                    ->label('Rename')
                    ->color('secondary')
                    ->authorize(fn($record) => auth()->user()->can('update', $record)),
                    
                Action::make('delete')
                    ->label('Delete')
                    ->color('danger')
                    ->requiresConfirmation('Delete File', 'This action cannot be undone.')
                    ->authorize(fn($record) => auth()->user()->can('delete', $record)),
            ])
            ->bulkActions([
                BulkAction::make('download_zip')
                    ->label('Download as ZIP')
                    ->color('primary')
                    ->authorize(fn() => auth()->user()->can('download-files')),
                    
                BulkAction::make('move_to_folder')
                    ->label('Move to Folder')
                    ->color('secondary')
                    ->authorize(fn() => auth()->user()->can('move-files')),
                    
                BulkAction::make('delete')
                    ->label('Delete Selected')
                    ->color('danger')
                    ->requiresConfirmation('Delete Files', 'This will permanently delete all selected files.')
                    ->authorize(fn() => auth()->user()->can('bulk-delete-files')),
            ])
            ->headerActions([
                Action::make('upload')
                    ->label('Upload Files')
                    ->color('primary'),
                    
                Action::make('create_folder')
                    ->label('New Folder')
                    ->color('secondary'),
            ])
            ->searchable()
            ->defaultSort('created_at', 'desc')
            ->paginate(30);
    }

    private function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    private function isPreviewable($type)
    {
        return str_contains($type, 'image') || 
               str_contains($type, 'video') || 
               str_contains($type, 'audio') ||
               $type === 'application/pdf';
    }
}
```

## Custom Column Examples

### Avatar Column

```php
<?php

namespace App\Tables\Columns;

use Egmond\InertiaTables\Columns\BaseColumn;

class AvatarColumn extends BaseColumn
{
    protected string $type = 'avatar';
    protected int $size = 40;
    protected bool $rounded = true;
    protected ?string $fallback = null;
    
    public function size(int $size): static
    {
        $this->size = $size;
        return $this;
    }
    
    public function fallback(string $fallback): static
    {
        $this->fallback = $fallback;
        return $this;
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
AvatarColumn::make('avatar')
    ->size(50)
    ->fallback('/images/default-avatar.png'),
```

React component:

```tsx
const AvatarColumn = ({ value, config }) => (
  <img
    src={value || config.fallback}
    alt="Avatar"
    className={`${config.rounded ? 'rounded-full' : 'rounded'}`}
    style={{ width: config.size, height: config.size }}
    onError={(e) => {
      if (config.fallback) {
        e.currentTarget.src = config.fallback;
      }
    }}
  />
);
```

## Performance Optimization Examples

### Large Dataset Handling

```php
public function table(Table $table): Table
{
    return $table->as('large_dataset')
        ->query($this->optimizedQuery())
        ->columns([
            TextColumn::make('name')->searchable(),
            TextColumn::make('created_at')->sortable(),
        ])
        ->searchable()
        ->paginate(50); // Larger page size for better performance
}

private function optimizedQuery()
{
    return User::query()
        ->select(['id', 'name', 'email', 'created_at']) // Only select needed columns
        ->when(request('search'), function ($query, $search) {
            // Use database-specific optimizations
            return $query->whereRaw("MATCH(name, email) AGAINST(? IN BOOLEAN MODE)", [$search]);
        })
        ->when(!request('search'), function ($query) {
            // Add default ordering only when not searching
            return $query->orderBy('created_at', 'desc');
        });
}
```

### Cached Tables

```php
public function table(Table $table): Table
{
    $cacheKey = 'users_table_' . md5(serialize(request()->all()));
    
    $result = cache()->remember($cacheKey, 300, function () use ($table) {
        return $table->as('cached_users')
            ->query(User::query())
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('email'),
            ])
            ->build();
    });
    
    return $result;
}
```

## Best Practices Summary

### 1. Security
- Always authorize actions with proper permissions
- Validate and sanitize all user inputs
- Use CSRF protection (automatic with Inertia)
- Implement proper error handling

### 2. Performance
- Add database indexes for sortable/searchable columns
- Use eager loading for relationships
- Implement caching for expensive queries
- Consider pagination limits for large datasets

### 3. User Experience
- Provide clear feedback for all actions
- Use appropriate confirmation dialogs for destructive actions
- Implement loading states for better perceived performance
- Design responsive tables for mobile devices

### 4. Code Organization
- Create reusable column and action classes
- Use traits for common functionality
- Organize table classes in dedicated namespaces
- Write comprehensive tests for table logic

These examples demonstrate the flexibility and power of Inertia Tables for building sophisticated data management interfaces. Each pattern can be adapted and extended based on your specific requirements.

## Next Steps

- **[Installation](/01-installation)** - Get started with Inertia Tables
- **[Getting Started](/02-getting-started)** - Basic implementation tutorial
- **[API Reference](/09-api-reference)** - Complete API documentation