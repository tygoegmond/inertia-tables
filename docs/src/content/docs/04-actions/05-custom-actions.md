---
title: Custom Actions
description: Learn how to create custom action types to extend Inertia Tables with specialized functionality.
---

## Overview

While Inertia Tables provides `Action` and `BulkAction` classes that cover most use cases, you may need to create custom action types for specialized functionality. Custom actions allow you to encapsulate complex behavior, create reusable components, and extend the action system.

## Creating Custom Actions

### Basic Custom Action

Create a custom action by extending `AbstractAction`:

```php
<?php

namespace App\Tables\Actions;

use Egmond\InertiaTables\Actions\AbstractAction;
use Egmond\InertiaTables\Actions\Contracts\ArrayableAction;
use Egmond\InertiaTables\Actions\Contracts\CallbackAction;
use Egmond\InertiaTables\Actions\Contracts\ExecutableAction;
use Illuminate\Contracts\Support\Arrayable;

class EmailAction extends AbstractAction implements Arrayable, ArrayableAction, CallbackAction, ExecutableAction
{
    use Concerns\ExecutesAction;
    use Concerns\HasCallback;
    use Concerns\SerializesToArray;
    
    protected string $emailTemplate = 'default';
    protected array $emailData = [];
    protected bool $queueEmail = false;
    
    public function template(string $template): static
    {
        $this->emailTemplate = $template;
        return $this;
    }
    
    public function data(array $data): static
    {
        $this->emailData = $data;
        return $this;
    }
    
    public function queue(bool $queue = true): static
    {
        $this->queueEmail = $queue;
        return $this;
    }
    
    protected function getAdditionalArrayData(): array
    {
        return [
            'emailTemplate' => $this->emailTemplate,
            'emailData' => $this->emailData,
            'queueEmail' => $this->queueEmail,
        ];
    }
}
```

### Using Your Custom Action

```php
use App\Tables\Actions\EmailAction;

->actions([
    EmailAction::make('send_welcome')
        ->label('Send Welcome Email')
        ->template('emails.welcome')
        ->data(['company' => 'Acme Corp'])
        ->queue()
        ->color('primary'),
])
```

## Advanced Custom Action Examples

### API Integration Action

```php
<?php

namespace App\Tables\Actions;

use Egmond\InertiaTables\Actions\AbstractAction;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

class ApiSyncAction extends AbstractAction implements Arrayable, ArrayableAction, CallbackAction, ExecutableAction
{
    use Concerns\ExecutesAction;
    use Concerns\HasCallback;
    use Concerns\SerializesToArray;
    
    protected string $endpoint = '';
    protected string $method = 'POST';
    protected array $headers = [];
    protected bool $async = false;
    
    public function endpoint(string $endpoint): static
    {
        $this->endpoint = $endpoint;
        return $this;
    }
    
    public function method(string $method): static
    {
        $this->method = strtoupper($method);
        return $this;
    }
    
    public function headers(array $headers): static
    {
        $this->headers = $headers;
        return $this;
    }
    
    public function async(bool $async = true): static
    {
        $this->async = $async;
        return $this;
    }
    
    protected function getAdditionalArrayData(): array
    {
        return [
            'endpoint' => $this->endpoint,
            'method' => $this->method,
            'async' => $this->async,
        ];
    }
}
```

Usage:

```php
ApiSyncAction::make('sync_to_crm')
    ->label('Sync to CRM')
    ->endpoint('https://api.crm.com/contacts')
    ->method('POST')
    ->headers(['Authorization' => 'Bearer ' . config('crm.api_key')])
    ->async()
```

### Workflow Action

```php
<?php

namespace App\Tables\Actions;

class WorkflowAction extends AbstractAction implements Arrayable, ArrayableAction, CallbackAction, ExecutableAction
{
    use Concerns\ExecutesAction;
    use Concerns\HasCallback;
    use Concerns\SerializesToArray;
    
    protected array $steps = [];
    protected string $currentStep = '';
    protected bool $requiresAllSteps = true;
    
    public function steps(array $steps): static
    {
        $this->steps = $steps;
        return $this;
    }
    
    public function currentStep(string $step): static
    {
        $this->currentStep = $step;
        return $this;
    }
    
    public function allowPartialCompletion(): static
    {
        $this->requiresAllSteps = false;
        return $this;
    }
    
    public function getNextStep(string $currentStep): ?string
    {
        $currentIndex = array_search($currentStep, array_keys($this->steps));
        
        if ($currentIndex === false || $currentIndex === count($this->steps) - 1) {
            return null;
        }
        
        return array_keys($this->steps)[$currentIndex + 1];
    }
    
    protected function getAdditionalArrayData(): array
    {
        return [
            'steps' => $this->steps,
            'currentStep' => $this->currentStep,
            'requiresAllSteps' => $this->requiresAllSteps,
        ];
    }
}
```

Usage:

```php
WorkflowAction::make('approve_order')
    ->label('Approve Order')
    ->steps([
        'review' => 'Review Order Details',
        'check_inventory' => 'Check Inventory',
        'verify_payment' => 'Verify Payment',
        'approve' => 'Final Approval',
    ])
    ->currentStep('review')
    ->color('success')
```

### File Operation Action

```php
<?php

namespace App\Tables\Actions;

class FileAction extends AbstractAction implements Arrayable, ArrayableAction, CallbackAction, ExecutableAction
{
    use Concerns\ExecutesAction;
    use Concerns\HasCallback;
    use Concerns\SerializesToArray;
    
    protected string $operation = 'download';
    protected string $fileField = 'file_path';
    protected ?string $downloadName = null;
    protected array $allowedMimeTypes = [];
    
    public function download(string $fileField = 'file_path'): static
    {
        $this->operation = 'download';
        $this->fileField = $fileField;
        return $this;
    }
    
    public function preview(string $fileField = 'file_path'): static
    {
        $this->operation = 'preview';
        $this->fileField = $fileField;
        return $this;
    }
    
    public function delete(string $fileField = 'file_path'): static
    {
        $this->operation = 'delete';
        $this->fileField = $fileField;
        return $this;
    }
    
    public function downloadName(string $name): static
    {
        $this->downloadName = $name;
        return $this;
    }
    
    public function allowedMimeTypes(array $types): static
    {
        $this->allowedMimeTypes = $types;
        return $this;
    }
    
    protected function getAdditionalArrayData(): array
    {
        return [
            'operation' => $this->operation,
            'fileField' => $this->fileField,
            'downloadName' => $this->downloadName,
            'allowedMimeTypes' => $this->allowedMimeTypes,
        ];
    }
}
```

Usage:

```php
FileAction::make('download_attachment')
    ->label('Download')
    ->download('attachment_path')
    ->downloadName('document.pdf')
    ->allowedMimeTypes(['application/pdf', 'image/*'])
```

## Custom Bulk Actions

### Multi-Step Bulk Action

```php
<?php

namespace App\Tables\Actions;

use Egmond\InertiaTables\Actions\BulkAction;

class MultiStepBulkAction extends BulkAction
{
    protected array $steps = [];
    protected int $currentStepIndex = 0;
    protected array $stepData = [];
    
    public function steps(array $steps): static
    {
        $this->steps = $steps;
        return $this;
    }
    
    public function currentStep(int $index): static
    {
        $this->currentStepIndex = $index;
        return $this;
    }
    
    public function stepData(array $data): static
    {
        $this->stepData = $data;
        return $this;
    }
    
    public function getNextStep(): ?array
    {
        if ($this->currentStepIndex >= count($this->steps) - 1) {
            return null;
        }
        
        return $this->steps[$this->currentStepIndex + 1];
    }
    
    public function isLastStep(): bool
    {
        return $this->currentStepIndex === count($this->steps) - 1;
    }
    
    protected function getAdditionalArrayData(): array
    {
        return array_merge(parent::getAdditionalArrayData(), [
            'steps' => $this->steps,
            'currentStepIndex' => $this->currentStepIndex,
            'stepData' => $this->stepData,
            'isMultiStep' => true,
        ]);
    }
}
```

Usage:

```php
MultiStepBulkAction::make('process_orders')
    ->label('Process Selected Orders')
    ->steps([
        ['name' => 'validate', 'label' => 'Validate Orders'],
        ['name' => 'inventory', 'label' => 'Check Inventory'],
        ['name' => 'payment', 'label' => 'Process Payments'],
        ['name' => 'fulfill', 'label' => 'Fulfill Orders'],
    ])
    ->authorize(fn() => auth()->user()->can('process-orders'))
```

## Action Mixins and Traits

### Create Reusable Functionality

```php
<?php

namespace App\Tables\Actions\Concerns;

trait HasNotification
{
    protected bool $sendNotification = false;
    protected string $notificationClass = '';
    protected array $notificationData = [];
    
    public function notify(string $notificationClass, array $data = []): static
    {
        $this->sendNotification = true;
        $this->notificationClass = $notificationClass;
        $this->notificationData = $data;
        
        return $this;
    }
    
    protected function sendNotificationIfNeeded($recipient): void
    {
        if ($this->sendNotification && $this->notificationClass) {
            $notification = new $this->notificationClass($this->notificationData);
            $recipient->notify($notification);
        }
    }
}
```

Use in custom actions:

```php
<?php

namespace App\Tables\Actions;

class NotifiableAction extends AbstractAction implements Arrayable, ArrayableAction, CallbackAction, ExecutableAction
{
    use Concerns\ExecutesAction;
    use Concerns\HasCallback;
    use Concerns\SerializesToArray;
    use Concerns\HasNotification; // Your custom trait
    
    // Action implementation...
}
```

## Frontend Integration

### Custom React Components

Create corresponding React components for your custom actions:

```tsx
// resources/js/Components/Table/Actions/EmailAction.tsx
import React from 'react';
import { Button } from '@/Components/ui/button';
import { Mail } from 'lucide-react';

interface EmailActionProps {
  action: {
    name: string;
    label: string;
    color: string;
    emailTemplate: string;
    queueEmail: boolean;
  };
  onExecute: (actionName: string, data?: any) => void;
  disabled?: boolean;
}

export function EmailAction({ action, onExecute, disabled }: EmailActionProps) {
  const handleClick = () => {
    onExecute(action.name, {
      template: action.emailTemplate,
      queue: action.queueEmail,
    });
  };

  return (
    <Button
      variant={action.color === 'primary' ? 'default' : 'outline'}
      size="sm"
      onClick={handleClick}
      disabled={disabled}
      className="inline-flex items-center gap-2"
    >
      <Mail className="h-4 w-4" />
      {action.label}
    </Button>
  );
}
```

### Register Custom Action Renderers

```tsx
import { InertiaTable } from '@tygoegmond/inertia-tables-react';
import { EmailAction } from './Components/Table/Actions/EmailAction';
import { WorkflowAction } from './Components/Table/Actions/WorkflowAction';

const customActionRenderers = {
  email: EmailAction,
  workflow: WorkflowAction,
};

export default function MyTable({ tableData }) {
  return (
    <InertiaTable
      state={tableData}
      customActionRenderers={customActionRenderers}
    />
  );
}
```

## Action Validation

### Input Validation

```php
<?php

namespace App\Tables\Actions;

class ValidatedAction extends AbstractAction implements Arrayable, ArrayableAction, CallbackAction, ExecutableAction
{
    use Concerns\ExecutesAction;
    use Concerns\HasCallback;
    use Concerns\SerializesToArray;
    
    protected array $validationRules = [];
    protected array $validationMessages = [];
    
    public function rules(array $rules): static
    {
        $this->validationRules = $rules;
        return $this;
    }
    
    public function messages(array $messages): static
    {
        $this->validationMessages = $messages;
        return $this;
    }
    
    public function validate(array $data): array
    {
        return validator($data, $this->validationRules, $this->validationMessages)
            ->validate();
    }
    
    protected function getAdditionalArrayData(): array
    {
        return [
            'validationRules' => $this->validationRules,
            'validationMessages' => $this->validationMessages,
        ];
    }
}
```

## Testing Custom Actions

```php
<?php

namespace Tests\Unit\Tables\Actions;

use App\Tables\Actions\EmailAction;
use Tests\TestCase;

class EmailActionTest extends TestCase
{
    public function test_email_action_sets_template()
    {
        $action = EmailAction::make('send_email')
            ->template('emails.welcome');
        
        $data = $action->toArray();
        
        $this->assertEquals('emails.welcome', $data['emailTemplate']);
    }
    
    public function test_email_action_can_be_queued()
    {
        $action = EmailAction::make('send_email')->queue();
        
        $data = $action->toArray();
        
        $this->assertTrue($data['queueEmail']);
    }
}
```

## Best Practices

### 1. Clear Naming Conventions

```php
// Good: Descriptive class names
class EmailAction extends AbstractAction { }
class WorkflowAction extends AbstractAction { }
class ApiSyncAction extends AbstractAction { }

// Bad: Generic names
class CustomAction extends AbstractAction { }
class MyAction extends AbstractAction { }
```

### 2. Implement Required Interfaces

```php
class MyCustomAction extends AbstractAction implements 
    Arrayable, 
    ArrayableAction, 
    CallbackAction, 
    ExecutableAction
{
    // Always include these for proper functionality
    use Concerns\ExecutesAction;
    use Concerns\HasCallback;
    use Concerns\SerializesToArray;
}
```

### 3. Provide Sensible Defaults

```php
public function __construct(string $name)
{
    parent::__construct($name);
    
    // Set sensible defaults
    $this->color = 'primary';
    $this->emailTemplate = 'default';
    $this->queueEmail = false;
}
```

### 4. Add Method Documentation

```php
/**
 * Set the email template to use for this action.
 * 
 * @param string $template The blade template path
 * @return static
 */
public function template(string $template): static
{
    $this->emailTemplate = $template;
    return $this;
}
```

## Action Package Structure

For reusable custom actions, consider this package structure:

```
src/
├── Actions/
│   ├── EmailAction.php
│   ├── WorkflowAction.php
│   └── Concerns/
│       ├── HasNotification.php
│       └── HasValidation.php
├── React/
│   ├── EmailAction.tsx
│   └── WorkflowAction.tsx
└── ActionServiceProvider.php
```

## Next Steps

Now that you understand custom actions, explore other table features:

- **[Search & Filtering](/05-search-and-filtering)** - Advanced search capabilities
- **[React Integration](/07-react-integration)** - Frontend customization and styling
- **[Advanced Usage](/08-advanced-usage)** - Performance optimization and advanced patterns

## Custom Action Reference

### Required Implementation

```php
// Minimal custom action structure
class MyAction extends AbstractAction implements 
    Arrayable, ArrayableAction, CallbackAction, ExecutableAction
{
    use Concerns\ExecutesAction;
    use Concerns\HasCallback;
    use Concerns\SerializesToArray;
    
    // Your custom properties and methods
    
    protected function getAdditionalArrayData(): array
    {
        return [
            // Your custom data for frontend
        ];
    }
}
```

### Available Traits

| Trait | Description | Use Case |
|-------|-------------|----------|
| `ExecutesAction` | Basic action execution | Required for all actions |
| `HasCallback` | Action callback handling | Required for all actions |
| `SerializesToArray` | Array serialization | Required for all actions |
| `CanBeDisabled` | Enable/disable actions | Conditional actions |
| `CanBeHidden` | Show/hide actions | Conditional visibility |
| `HasAuthorization` | Authorization logic | Permission-based actions |
| `HasColor` | Color variants | Visual customization |
| `HasConfirmation` | Confirmation dialogs | Destructive actions |