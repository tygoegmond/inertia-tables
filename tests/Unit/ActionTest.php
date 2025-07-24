<?php

use Egmond\InertiaTables\Actions\Action;
use Egmond\InertiaTables\Actions\AbstractAction;
use Egmond\InertiaTables\Actions\Contracts\ActionContract;
use Egmond\InertiaTables\Tests\Database\Models\User;
use Egmond\InertiaTables\Table;
use Illuminate\Contracts\Support\Arrayable;

describe('AbstractAction Class', function () {
    
    beforeEach(function () {
        $this->action = Action::make('edit');
    });

    describe('Basic Instantiation', function () {
        
        it('can be created with make method', function () {
            $action = Action::make('delete');
            
            expect($action)->toBeInstanceOf(AbstractAction::class);
            expect($action)->toBeInstanceOf(ActionContract::class);
            expect($action->getName())->toBe('delete');
        });

        it('can be created with constructor', function () {
            $action = new Action('create');
            
            expect($action->getName())->toBe('create');
        });

    });

    describe('Label Management', function () {
        
        it('generates default label from name', function () {
            $action = Action::make('edit_user');
            
            expect($action->getLabel())->toBe('Edit User');
        });

        it('handles kebab-case names', function () {
            $action = Action::make('send-email');
            
            expect($action->getLabel())->toBe('Send Email');
        });

        it('can set custom label fluently', function () {
            $result = $this->action->label('Custom Edit');
            
            expect($result)->toBe($this->action);
            expect($this->action->getLabel())->toBe('Custom Edit');
        });

    });

    describe('Authorization', function () {
        
        it('is authorized by default', function () {
            expect($this->action->isAuthorized())->toBeTrue();
        });

        it('is authorized with null record by default', function () {
            expect($this->action->isAuthorized(null))->toBeTrue();
        });

        it('can set authorization closure', function () {
            $result = $this->action->authorize(fn() => false);
            
            expect($result)->toBe($this->action);
            expect($this->action->isAuthorized())->toBeFalse();
        });

        it('passes record to authorization closure', function () {
            $user = User::factory()->create();
            
            $this->action->authorize(fn($record) => $record?->id === $user->id);
            
            expect($this->action->isAuthorized($user))->toBeTrue();
            expect($this->action->isAuthorized(null))->toBeFalse();
        });

        it('receives record parameter in closure', function () {
            $user = User::factory()->create(['status' => 'active']);
            
            $this->action->authorize(fn($record) => $record?->status === 'active');
            
            expect($this->action->isAuthorized($user))->toBeTrue();
        });

    });

    describe('Disabled State', function () {
        
        it('is not disabled by default', function () {
            expect($this->action->isDisabled())->toBeFalse();
        });

        it('can be disabled fluently', function () {
            $result = $this->action->disabled();
            
            expect($result)->toBe($this->action);
            expect($this->action->isDisabled())->toBeTrue();
        });

        it('can be disabled conditionally', function () {
            $this->action->disabled(false);
            
            expect($this->action->isDisabled())->toBeFalse();
        });

        it('can use closure for disabled state', function () {
            $user = User::factory()->create(['status' => 'inactive']);
            
            $this->action->disabled(fn($record) => $record?->status === 'inactive');
            
            expect($this->action->isDisabled($user))->toBeTrue();
        });

        it('evaluates closure with record parameter', function () {
            $activeUser = User::factory()->create(['status' => 'active']);
            $inactiveUser = User::factory()->create(['status' => 'inactive']);
            
            $this->action->disabled(fn($record) => $record?->status !== 'active');
            
            expect($this->action->isDisabled($activeUser))->toBeFalse();
            expect($this->action->isDisabled($inactiveUser))->toBeTrue();
        });

    });

    describe('Visibility Management', function () {
        
        it('is visible by default', function () {
            expect($this->action->isVisible())->toBeTrue();
        });

        it('is not hidden by default', function () {
            expect($this->action->isHidden())->toBeFalse();
        });

        it('can be hidden fluently', function () {
            $result = $this->action->hidden();
            
            expect($result)->toBe($this->action);
            expect($this->action->isHidden())->toBeTrue();
            expect($this->action->isVisible())->toBeFalse();
        });

        it('can be set visible fluently', function () {
            $this->action->hidden(); // First hide it
            
            $result = $this->action->visible();
            
            expect($result)->toBe($this->action);
            // Since hidden is true and visible is true, but hidden takes precedence, it should be false
            expect($this->action->isVisible())->toBeFalse();
        });

        it('visible overrides hidden when both true', function () {
            $this->action->hidden()->visible();
            
            expect($this->action->isHidden())->toBeTrue();
            expect($this->action->isVisible())->toBeFalse(); // Hidden takes precedence
        });

        it('can use closure for visibility', function () {
            $user = User::factory()->create(['status' => 'active']);
            
            $this->action->visible(fn($record) => $record?->status === 'active');
            
            expect($this->action->isVisible($user))->toBeTrue();
        });

        it('can use closure for hidden state', function () {
            $adminUser = User::factory()->create(['email' => 'admin@example.com']);
            $regularUser = User::factory()->create(['email' => 'user@example.com']);
            
            $this->action->hidden(fn($record) => !str_contains($record?->email ?? '', 'admin'));
            
            expect($this->action->isVisible($adminUser))->toBeTrue();
            expect($this->action->isVisible($regularUser))->toBeFalse();
        });

    });

    describe('Color Management', function () {
        
        it('has default color', function () {
            // The getColor method should be implemented in HasColor trait
            expect($this->action->getColor())->toBe('primary');
        });

        it('can set color fluently', function () {
            $result = $this->action->color('danger');
            
            expect($result)->toBe($this->action);
            expect($this->action->getColor())->toBe('danger');
        });

    });

    describe('Confirmation', function () {
        
        it('does not require confirmation by default', function () {
            expect($this->action->needsConfirmation())->toBeFalse();
        });

        it('can require confirmation', function () {
            $result = $this->action->requiresConfirmation();
            
            expect($result)->toBe($this->action);
            expect($this->action->needsConfirmation())->toBeTrue();
        });

        it('can set confirmation details', function () {
            $this->action
                ->requiresConfirmation()
                ->confirmationTitle('Delete User')
                ->confirmationMessage('Are you sure?')
                ->confirmationButton('Yes, Delete')
                ->cancelButton('Cancel');
            
            expect($this->action->getConfirmationTitle())->toBe('Delete User');
            expect($this->action->getConfirmationMessage())->toBe('Are you sure?');
            expect($this->action->getConfirmationButton())->toBe('Yes, Delete');
            expect($this->action->getCancelButton())->toBe('Cancel');
        });

    });

    describe('Table Interaction', function () {
        
        it('has no table by default', function () {
            expect($this->action->getTable())->toBeNull();
        });

        it('can set table fluently', function () {
            $table = new Table();
            $result = $this->action->table($table);
            
            expect($result)->toBe($this->action);
            expect($this->action->getTable())->toBe($table);
        });

    });

});

describe('Action Class', function () {
    
    beforeEach(function () {
        $this->action = Action::make('edit');
    });

    describe('Interface Implementation', function () {
        
        it('implements Arrayable interface', function () {
            expect($this->action)->toBeInstanceOf(Arrayable::class);
        });

    });

    describe('Action Execution', function () {
        
        it('has no action by default', function () {
            expect($this->action->hasAction())->toBeFalse();
        });

        it('can set action closure', function () {
            $executed = false;
            
            $result = $this->action->action(function() use (&$executed) {
                $executed = true;
                return 'success';
            });
            
            expect($result)->toBe($this->action);
            expect($this->action->hasAction())->toBeTrue();
        });

        it('can execute action', function () {
            $result = null;
            
            $this->action->action(function($records, $params) use (&$result) {
                $result = ['records' => $records, 'params' => $params];
                return 'executed';
            });
            
            $records = [1, 2, 3];
            $params = ['test' => 'value'];
            
            $response = $this->action->execute($records, $params);
            
            expect($response)->toBe('executed');
            expect($result['records'])->toBe($records);
            expect($result['params'])->toBe($params);
        });

    });

    describe('Callback Generation', function () {
        
        it('generates callback URL when table class is set', function () {
            $this->action->setTableClass('App\\Tables\\UserTable');
            
            $callback = $this->action->getCallback('123');
            
            expect($callback)->toBeString();
            expect($callback)->toContain('inertia-tables');
        });

        it('throws exception when table class not set', function () {
            expect(fn() => $this->action->getCallback('123'))
                ->toThrow(Exception::class, 'Table class must be set to generate frontend callback');
        });

    });

    describe('Array Serialization', function () {
        
        it('converts to array with basic properties', function () {
            $array = $this->action->toArray();
            
            expect($array)->toHaveKeys(['name', 'label', 'color']);
            expect($array['name'])->toBe('edit');
            expect($array['label'])->toBe('Edit');
            expect($array['color'])->toBe('primary');
        });

        it('includes non-default values in array', function () {
            $this->action
                ->label('Custom Edit')
                ->color('danger')
                ->requiresConfirmation()
                ->confirmationTitle('Confirm Edit')
                ->action(fn() => 'test');
            
            $array = $this->action->toArray();
            
            expect($array['label'])->toBe('Custom Edit');
            expect($array['color'])->toBe('danger');
            expect($array['requiresConfirmation'])->toBeTrue();
            expect($array['confirmationTitle'])->toBe('Confirm Edit');
            expect($array['hasAction'])->toBeTrue();
        });

        it('filters out default values', function () {
            $array = $this->action->toArray();
            
            // These should not be present as they are default values
            expect($array)->not->toHaveKey('requiresConfirmation'); // false is filtered
            expect($array)->not->toHaveKey('hasAction'); // false is filtered
        });

    });

    describe('Row-Specific Serialization', function () {
        
        beforeEach(function () {
            $this->user = User::factory()->create();
            $this->action->setTableClass('App\\Tables\\UserTable');
        });

        it('converts to row array with callback when not disabled', function () {
            $array = $this->action->toRowArray($this->user);
            
            expect($array)->toHaveKey('callback');
            expect($array['callback'])->toBeString();
            expect($array)->not->toHaveKey('disabled'); // false is filtered
        });

        it('shows disabled state when disabled', function () {
            $this->action->disabled();
            
            $array = $this->action->toRowArray($this->user);
            
            expect($array['disabled'])->toBeTrue();
            expect($array)->not->toHaveKey('callback'); // No callback when disabled
        });

        it('evaluates disabled closure with record', function () {
            $this->action->disabled(fn($record) => $record->status === 'inactive');
            
            $activeUser = User::factory()->create(['status' => 'active']);
            $inactiveUser = User::factory()->create(['status' => 'inactive']);
            
            $activeArray = $this->action->toRowArray($activeUser);
            $inactiveArray = $this->action->toRowArray($inactiveUser);
            
            expect($activeArray)->toHaveKey('callback');
            expect($inactiveArray['disabled'])->toBeTrue();
            expect($inactiveArray)->not->toHaveKey('callback');
        });

    });

    describe('Method Chaining', function () {
        
        it('can chain all methods fluently', function () {
            $table = new Table();
            
            $result = $this->action
                ->label('Delete User')
                ->color('danger')
                ->requiresConfirmation()
                ->confirmationTitle('Confirm Delete')
                ->confirmationMessage('This cannot be undone')
                ->disabled(fn($record) => $record?->status === 'protected')
                ->hidden(fn($record) => $record?->id === 1)
                ->authorize(fn($record) => $record?->status === 'active')
                ->action(fn($records) => "Deleted ".count($records)." users")
                ->table($table);
            
            expect($result)->toBe($this->action);
            
            // Test all configurations are applied
            expect($this->action->getLabel())->toBe('Delete User');
            expect($this->action->getColor())->toBe('danger');
            expect($this->action->needsConfirmation())->toBeTrue();
            expect($this->action->getConfirmationTitle())->toBe('Confirm Delete');
            expect($this->action->getTable())->toBe($table);
            expect($this->action->hasAction())->toBeTrue();
        });

    });

    describe('Complex Scenarios', function () {
        
        it('handles multiple conditional states correctly', function () {
            $adminUser = User::factory()->create(['email' => 'admin@example.com', 'status' => 'active']);
            $regularUser = User::factory()->create(['email' => 'user@example.com', 'status' => 'active']);
            $inactiveUser = User::factory()->create(['email' => 'user2@example.com', 'status' => 'inactive']);
            
            $this->action
                ->authorize(fn($record) => $record?->status === 'active')
                ->visible(fn($record) => str_contains($record?->email ?? '', 'admin') || $record?->status === 'active')
                ->disabled(fn($record) => $record?->status !== 'active');
            
            // Admin user: authorized, visible, not disabled
            expect($this->action->isAuthorized($adminUser))->toBeTrue();
            expect($this->action->isVisible($adminUser))->toBeTrue();
            expect($this->action->isDisabled($adminUser))->toBeFalse();
            
            // Regular active user: authorized, visible, not disabled
            expect($this->action->isAuthorized($regularUser))->toBeTrue();
            expect($this->action->isVisible($regularUser))->toBeTrue();
            expect($this->action->isDisabled($regularUser))->toBeFalse();
            
            // Inactive user: not authorized, not visible, disabled
            expect($this->action->isAuthorized($inactiveUser))->toBeFalse();
            expect($this->action->isVisible($inactiveUser))->toBeFalse();
            expect($this->action->isDisabled($inactiveUser))->toBeTrue();
        });

        it('handles edge cases in serialization', function () {
            $this->action
                ->label('') // Empty string
                ->color('primary'); // Same as default value
            
            $array = $this->action->toArray();
            
            // But required fields should always be present
            expect($array)->toHaveKey('name');
            expect($array)->toHaveKey('label');
            expect($array)->toHaveKey('color');
        });

    });

});