<?php

use Egmond\InertiaTables\Actions\AbstractAction;
use Egmond\InertiaTables\Actions\BulkAction;
use Egmond\InertiaTables\Actions\Contracts\ActionContract;
use Egmond\InertiaTables\Tests\Database\Models\User;
use Illuminate\Contracts\Support\Arrayable;

describe('BulkAction Class', function () {

    beforeEach(function () {
        $this->bulkAction = BulkAction::make('delete');
    });

    describe('Basic Functionality', function () {

        it('extends AbstractAction', function () {
            expect($this->bulkAction)->toBeInstanceOf(AbstractAction::class);
            expect($this->bulkAction)->toBeInstanceOf(ActionContract::class);
        });

        it('implements required interfaces', function () {
            expect($this->bulkAction)->toBeInstanceOf(Arrayable::class);
        });

        it('can be created with make method', function () {
            $action = BulkAction::make('archive');

            expect($action)->toBeInstanceOf(BulkAction::class);
            expect($action->getName())->toBe('archive');
        });

    });

    describe('Authorization Requirements', function () {

        it('throws exception when no authorization is set', function () {
            expect(fn () => $this->bulkAction->isAuthorized())
                ->toThrow(Exception::class, "BulkAction 'delete' must have an authorize() method defined for security purposes.");
        });

        it('throws exception when checking authorization with record but no authorize method', function () {
            $user = User::factory()->create();

            expect(fn () => $this->bulkAction->isAuthorized($user))
                ->toThrow(Exception::class, "BulkAction 'delete' must have an authorize() method defined for security purposes.");
        });

        it('works correctly when authorization is set', function () {
            $this->bulkAction->authorize(fn () => true);

            expect($this->bulkAction->isAuthorized())->toBeTrue();
        });

        it('respects authorization logic', function () {
            $this->bulkAction->authorize(fn () => false);

            expect($this->bulkAction->isAuthorized())->toBeFalse();
        });

        it('passes record to authorization closure', function () {
            $user = User::factory()->create(['status' => 'active']);

            $this->bulkAction->authorize(fn ($record) => $record?->status === 'active');

            expect($this->bulkAction->isAuthorized($user))->toBeTrue();
        });

    });

    describe('Disabled State Handling', function () {

        it('is not disabled by default', function () {
            expect($this->bulkAction->isDisabled())->toBeFalse();
        });

        it('can be disabled', function () {
            $this->bulkAction->disabled();

            expect($this->bulkAction->isDisabled())->toBeTrue();
        });

        it('can use closure for disabled state', function () {
            $user = User::factory()->create(['status' => 'protected']);

            $this->bulkAction->disabled(fn ($record) => $record?->status === 'protected');

            expect($this->bulkAction->isDisabled($user))->toBeTrue();
        });

    });

    describe('Action Execution', function () {

        beforeEach(function () {
            $this->bulkAction->authorize(fn () => true); // Required for bulk actions
        });

        it('has no action by default', function () {
            expect($this->bulkAction->hasAction())->toBeFalse();
        });

        it('can set and execute action', function () {
            $executed = false;
            $receivedRecords = null;
            $receivedParams = null;

            $this->bulkAction->action(function ($records, $params) use (&$executed, &$receivedRecords, &$receivedParams) {
                $executed = true;
                $receivedRecords = $records;
                $receivedParams = $params;

                return 'success';
            });

            expect($this->bulkAction->hasAction())->toBeTrue();

            $records = [1, 2, 3];
            $params = ['confirm' => true];
            $result = $this->bulkAction->execute($records, $params);

            expect($executed)->toBeTrue();
            expect($receivedRecords)->toBe($records);
            expect($receivedParams)->toBe($params);
            expect($result)->toBe('success');
        });

    });

    describe('Array Serialization', function () {

        beforeEach(function () {
            $this->bulkAction
                ->authorize(fn () => true)
                ->setTableClass('App\\Tables\\UserTable');
        });

        it('includes basic properties in array', function () {
            $array = $this->bulkAction->toArray();

            expect($array)->toHaveKeys(['name', 'label', 'color']);
            expect($array['name'])->toBe('delete');
            expect($array['label'])->toBe('Delete');
            expect($array['color'])->toBe('primary');
        });

        it('includes bulk action specific properties', function () {
            $array = $this->bulkAction->toArray();

            // Disabled is false and gets filtered out by default
            expect($array)->not->toHaveKey('disabled');
            // Callback is available when not disabled and table class is set
            expect($array)->toHaveKey('callback');
            expect($array['callback'])->toBeString();
        });

        it('shows disabled state correctly', function () {
            $this->bulkAction->disabled();

            $array = $this->bulkAction->toArray();

            expect($array['disabled'])->toBeTrue();
            expect($array)->not->toHaveKey('callback'); // Callback is null so gets filtered out
        });

        it('includes confirmation details', function () {
            $this->bulkAction
                ->requiresConfirmation()
                ->confirmationTitle('Delete Records')
                ->confirmationMessage('This will permanently delete the selected records.');

            $array = $this->bulkAction->toArray();

            expect($array['requiresConfirmation'])->toBeTrue();
            expect($array['confirmationTitle'])->toBe('Delete Records');
            expect($array['confirmationMessage'])->toBe('This will permanently delete the selected records.');
        });

        it('includes action state', function () {
            $this->bulkAction->action(fn ($records) => 'deleted');

            $array = $this->bulkAction->toArray();

            expect($array['hasAction'])->toBeTrue();
        });

    });

    describe('Callback Generation', function () {

        beforeEach(function () {
            $this->bulkAction->authorize(fn () => true);
        });

        it('generates callback when table class is set', function () {
            $this->bulkAction->setTableClass('App\\Tables\\UserTable');

            $callback = $this->bulkAction->getCallback();

            expect($callback)->toBeString();
            expect($callback)->toContain('inertia-tables');
        });

        it('throws exception when table class not set', function () {
            expect(fn () => $this->bulkAction->getCallback())
                ->toThrow(Exception::class, 'Table class must be set to generate frontend callback');
        });

        it('includes callback in array when not disabled', function () {
            $this->bulkAction->setTableClass('App\\Tables\\UserTable');

            $array = $this->bulkAction->toArray();

            expect($array['callback'])->toBeString();
        });

        it('excludes callback when disabled', function () {
            $this->bulkAction
                ->setTableClass('App\\Tables\\UserTable')
                ->disabled();

            $array = $this->bulkAction->toArray();

            expect($array)->not->toHaveKey('callback'); // Null values get filtered out
        });

    });

    describe('Method Chaining', function () {

        it('can chain all methods fluently', function () {
            $result = $this->bulkAction
                ->label('Delete Selected')
                ->color('danger')
                ->requiresConfirmation()
                ->confirmationTitle('Confirm Bulk Delete')
                ->confirmationMessage('This will delete all selected records permanently.')
                ->authorize(fn () => auth()->user()?->can('delete-records'))
                ->disabled(fn () => ! auth()->check())
                ->action(fn ($records) => 'Deleted '.count($records).' records')
                ->setTableClass('App\\Tables\\UserTable');

            expect($result)->toBe($this->bulkAction);

            // Test configurations are applied
            expect($this->bulkAction->getLabel())->toBe('Delete Selected');
            expect($this->bulkAction->getColor())->toBe('danger');
            expect($this->bulkAction->needsConfirmation())->toBeTrue();
            expect($this->bulkAction->getConfirmationTitle())->toBe('Confirm Bulk Delete');
            expect($this->bulkAction->hasAction())->toBeTrue();
        });

    });

    describe('Security Features', function () {

        it('enforces authorization requirement for security', function () {
            // This is a key security feature - bulk actions MUST have authorization
            expect(fn () => $this->bulkAction->isAuthorized())
                ->toThrow(Exception::class);
        });

        it('allows complex authorization logic', function () {
            $adminUser = User::factory()->create(['email' => 'admin@example.com']);
            $regularUser = User::factory()->create(['email' => 'user@example.com']);

            $this->bulkAction->authorize(function ($record) {
                // Simple authorization logic for testing
                return ! str_contains($record?->email ?? '', 'admin');
            });

            expect($this->bulkAction->isAuthorized($regularUser))->toBeTrue();
            expect($this->bulkAction->isAuthorized($adminUser))->toBeFalse();
        });

    });

    describe('Color Variants', function () {

        beforeEach(function () {
            $this->bulkAction->authorize(fn () => true);
        });

        it('can use danger color variant', function () {
            $this->bulkAction
                ->setTableClass('App\\Tables\\UserTable')
                ->danger();

            expect($this->bulkAction->getColor())->toBe('danger');

            $array = $this->bulkAction->toArray();
            expect($array['color'])->toBe('danger');
        });

        it('can use warning color variant', function () {
            $this->bulkAction->warning();

            expect($this->bulkAction->getColor())->toBe('warning');
        });

        it('can use success color variant', function () {
            $this->bulkAction->success();

            expect($this->bulkAction->getColor())->toBe('success');
        });

    });

    describe('Real-world Scenarios', function () {

        beforeEach(function () {
            $this->users = User::factory()->count(5)->create();
        });

        it('handles bulk delete scenario', function () {
            $deletedCount = 0;

            $deleteAction = BulkAction::make('delete')
                ->label('Delete Selected Users')
                ->color('danger')
                ->requiresConfirmation()
                ->confirmationTitle('Delete Users')
                ->confirmationMessage('This action cannot be undone.')
                ->authorize(fn () => true) // In real app: auth()->user()->can('delete-users')
                ->action(function ($recordIds) use (&$deletedCount) {
                    $deletedCount = count($recordIds);

                    return "Deleted {$deletedCount} users";
                })
                ->setTableClass('App\\Tables\\UserTable');

            $recordIds = $this->users->pluck('id')->toArray();
            $result = $deleteAction->execute($recordIds, []);

            expect($result)->toBe('Deleted 5 users');
            expect($deletedCount)->toBe(5);

            $array = $deleteAction->toArray();
            expect($array['label'])->toBe('Delete Selected Users');
            expect($array['color'])->toBe('danger');
            expect($array['requiresConfirmation'])->toBeTrue();
            expect($array['hasAction'])->toBeTrue();
        });

        it('handles bulk archive scenario with conditional authorization', function () {
            $archiveAction = BulkAction::make('archive')
                ->label('Archive Selected')
                ->color('warning')
                ->authorize(function ($record) {
                    // Only allow archiving active users
                    return $record?->status === 'active';
                })
                ->action(fn ($recordIds) => 'Archived '.count($recordIds).' users')
                ->setTableClass('App\\Tables\\UserTable');

            $activeUser = User::factory()->create(['status' => 'active']);
            $inactiveUser = User::factory()->create(['status' => 'inactive']);

            expect($archiveAction->isAuthorized($activeUser))->toBeTrue();
            expect($archiveAction->isAuthorized($inactiveUser))->toBeFalse();
        });

    });

    describe('Edge Cases', function () {

        beforeEach(function () {
            $this->bulkAction->authorize(fn () => true);
        });

        it('handles empty record sets', function () {
            $this->bulkAction->action(fn ($records) => 'Processed '.count($records).' records');

            $result = $this->bulkAction->execute([], []);

            expect($result)->toBe('Processed 0 records');
        });

        it('handles large record sets', function () {
            $largeRecordSet = range(1, 1000);

            $this->bulkAction->action(fn ($records) => count($records));

            $result = $this->bulkAction->execute($largeRecordSet, []);

            expect($result)->toBe(1000);
        });

        it('filters out default values in serialization', function () {
            $this->bulkAction->setTableClass('App\\Tables\\UserTable');

            $array = $this->bulkAction->toArray();

            // Should not include false values
            expect($array)->not->toHaveKey('requiresConfirmation'); // false filtered out
            expect($array)->not->toHaveKey('hasAction'); // false filtered out
            expect($array)->not->toHaveKey('disabled'); // false filtered out

            // But should include callback when available
            expect($array)->toHaveKey('callback');
        });

    });

});
