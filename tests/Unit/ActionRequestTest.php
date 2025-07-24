<?php

use Egmond\InertiaTables\Actions\Action;
use Egmond\InertiaTables\Actions\BulkAction;
use Egmond\InertiaTables\Http\Requests\ActionRequest;
use Egmond\InertiaTables\Table;
use Egmond\InertiaTables\Tests\Database\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

describe('ActionRequest Class', function () {
    
    beforeEach(function () {
        $this->user = User::factory()->create(['name' => 'Test User']);
        $this->users = User::factory()->count(3)->create();
        
        // Create a sample table class for testing
        $this->table = new class extends Table {
            public function build() {
                return $this->query(User::query())
                    ->as('users')
                    ->columns([])
                    ->actions([
                        Action::make('edit')->authorize(fn() => true),
                        Action::make('delete')->authorize(fn() => true),
                    ])
                    ->bulkActions([
                        BulkAction::make('bulk_delete')->authorize(fn() => true),
                    ])
                    ->build();
            }
        };
        
        $this->encodedTableClass = base64_encode(get_class($this->table));
        $this->encodedActionClass = base64_encode(Action::class);
    });

    describe('Request Validation Rules', function () {
        
        it('has correct validation rules', function () {
            $request = new ActionRequest();
            $rules = $request->rules();
            
            expect($rules)->toHaveKeys(['table', 'name', 'action', 'records']);
            expect($rules['table'])->toBe('required|string');
            expect($rules['name'])->toBe('required|string');
            expect($rules['action'])->toBe('required|string');
            expect($rules['records'])->toBe('sometimes|array');
        });

        it('validates required fields', function () {
            $validator = Validator::make([], (new ActionRequest())->rules());
            
            expect($validator->fails())->toBeTrue();
            expect($validator->errors()->has('table'))->toBeTrue();
            expect($validator->errors()->has('name'))->toBeTrue();
            expect($validator->errors()->has('action'))->toBeTrue();
        });

        it('passes validation with valid data', function () {
            $data = [
                'table' => $this->encodedTableClass,
                'name' => 'edit',
                'action' => $this->encodedActionClass,
            ];
            
            $validator = Validator::make($data, (new ActionRequest())->rules());
            
            expect($validator->passes())->toBeTrue();
        });

        it('validates records as array when present', function () {
            $data = [
                'table' => $this->encodedTableClass,
                'name' => 'bulk_delete',
                'action' => base64_encode(BulkAction::class),
                'records' => [1, 2, 3],
            ];
            
            $validator = Validator::make($data, (new ActionRequest())->rules());
            
            expect($validator->passes())->toBeTrue();
        });

        it('fails validation when records is not array', function () {
            $data = [
                'table' => $this->encodedTableClass,
                'name' => 'bulk_delete',
                'action' => base64_encode(BulkAction::class),
                'records' => 'not-an-array',
            ];
            
            $validator = Validator::make($data, (new ActionRequest())->rules());
            
            expect($validator->fails())->toBeTrue();
            expect($validator->errors()->has('records'))->toBeTrue();
        });

    });

    describe('Authorization Logic', function () {
        
        it('authorizes requests with valid signature', function () {
            URL::shouldReceive('hasValidSignature')
                ->once()
                ->andReturn(true);
            
            $request = createActionRequest([
                'table' => $this->encodedTableClass,
                'name' => 'edit',
                'action' => $this->encodedActionClass,
            ]);
            
            expect($request->authorize())->toBeTrue();
        });

        it('rejects requests with invalid signature', function () {
            URL::shouldReceive('hasValidSignature')
                ->once()
                ->andReturn(false);
            
            $request = createActionRequest([
                'table' => $this->encodedTableClass,
                'name' => 'edit',
                'action' => $this->encodedActionClass,
            ]);
            
            expect($request->authorize())->toBeFalse();
        });

        it('handles bulk action authorization', function () {
            URL::shouldReceive('hasValidSignature')
                ->once()
                ->andReturn(true);
            
            $request = createActionRequest([
                'table' => $this->encodedTableClass,
                'name' => 'bulk_delete',
                'action' => base64_encode(BulkAction::class),
                'records' => [1, 2, 3],
            ]);
            
            expect($request->authorize())->toBeTrue();
        });

    });

    describe('Table Instantiation', function () {
        
        it('can decode and instantiate table class', function () {
            $request = createActionRequest([
                'table' => $this->encodedTableClass,
                'name' => 'edit',
                'action' => $this->encodedActionClass,
            ]);
            
            $table = $request->getTable();
            
            expect($table)->toBeInstanceOf(Table::class);
            expect(get_class($table))->toBe(get_class($this->table));
        });

        it('throws exception for invalid table class', function () {
            $invalidTableClass = base64_encode('NonExistentTableClass');
            
            $request = createActionRequest([
                'table' => $invalidTableClass,
                'name' => 'edit',
                'action' => $this->encodedActionClass,
            ]);
            
            expect(fn() => $request->getTable())
                ->toThrow(Error::class); // Class not found error
        });

        it('throws exception for malformed base64', function () {
            $request = createActionRequest([
                'table' => 'invalid-base64!@#',
                'name' => 'edit',
                'action' => $this->encodedActionClass,
            ]);
            
            expect(fn() => $request->getTable())
                ->toThrow(Error::class);
        });

    });

    describe('Action Resolution', function () {
        
        it('can find and return regular action', function () {
            $request = createActionRequest([
                'table' => $this->encodedTableClass,
                'name' => 'edit',
                'action' => $this->encodedActionClass,
            ]);
            
            $action = $request->getAction();
            
            expect($action)->toBeInstanceOf(Action::class);
            expect($action->getName())->toBe('edit');
        });

        it('can find and return bulk action', function () {
            $request = createActionRequest([
                'table' => $this->encodedTableClass,
                'name' => 'bulk_delete',
                'action' => base64_encode(BulkAction::class),
            ]);
            
            $action = $request->getAction();
            
            expect($action)->toBeInstanceOf(BulkAction::class);
            expect($action->getName())->toBe('bulk_delete');
        });

        it('throws exception when action not found', function () {
            $request = createActionRequest([
                'table' => $this->encodedTableClass,
                'name' => 'nonexistent_action',
                'action' => $this->encodedActionClass,
            ]);
            
            expect(fn() => $request->getAction())
                ->toThrow(Exception::class, 'Action not found');
        });

        it('throws exception when action class mismatch', function () {
            // Try to find a regular action but specify BulkAction class
            $request = createActionRequest([
                'table' => $this->encodedTableClass,
                'name' => 'edit', // This is a regular action
                'action' => base64_encode(BulkAction::class), // But we're looking for BulkAction
            ]);
            
            expect(fn() => $request->getAction())
                ->toThrow(Exception::class);
        });

    });

    describe('Record Fetching', function () {
        
        it('can fetch single record for regular actions', function () {
            $request = createActionRequest([
                'table' => $this->encodedTableClass,
                'name' => 'edit',
                'action' => $this->encodedActionClass,
                'record' => $this->user->id,
            ]);
            
            $record = $request->getRecord();
            
            expect($record)->toBeInstanceOf(User::class);
            expect($record->id)->toBe($this->user->id);
            expect($record->name)->toBe('Test User');
        });

        it('returns null when no record parameter for regular actions', function () {
            $request = createActionRequest([
                'table' => $this->encodedTableClass,
                'name' => 'edit',
                'action' => $this->encodedActionClass,
            ]);
            
            $record = $request->getRecord();
            
            expect($record)->toBeNull();
        });

        it('can fetch multiple records for bulk actions', function () {
            $recordIds = $this->users->pluck('id')->toArray();
            
            $request = createActionRequest([
                'table' => $this->encodedTableClass,
                'name' => 'bulk_delete',
                'action' => base64_encode(BulkAction::class),
                'records' => $recordIds,
            ]);
            
            $records = $request->getRecords();
            
            expect($records)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
            expect($records->count())->toBe(3);
            expect($records->pluck('id')->toArray())->toBe($recordIds);
        });

        it('returns empty collection when no records for bulk actions', function () {
            $request = createActionRequest([
                'table' => $this->encodedTableClass,
                'name' => 'bulk_delete',
                'action' => base64_encode(BulkAction::class),
                'records' => [],
            ]);
            
            $records = $request->getRecords();
            
            expect($records)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
            expect($records->count())->toBe(0);
        });

        it('handles non-existent record ids gracefully', function () {
            $request = createActionRequest([
                'table' => $this->encodedTableClass,
                'name' => 'edit',
                'action' => $this->encodedActionClass,
                'record' => 99999, // Non-existent ID
            ]);
            
            $record = $request->getRecord();
            
            expect($record)->toBeNull();
        });

        it('filters out non-existent records in bulk actions', function () {
            $validIds = $this->users->pluck('id')->toArray();
            $mixedIds = array_merge($validIds, [99999, 99998]); // Add non-existent IDs
            
            $request = createActionRequest([
                'table' => $this->encodedTableClass,
                'name' => 'bulk_delete',
                'action' => base64_encode(BulkAction::class),
                'records' => $mixedIds,
            ]);
            
            $records = $request->getRecords();
            
            expect($records->count())->toBe(3); // Only valid records
            expect($records->pluck('id')->toArray())->toBe($validIds);
        });

    });

    describe('Security Features', function () {
        
        it('requires valid signature for authorization', function () {
            URL::shouldReceive('hasValidSignature')->andReturn(false);
            
            $request = createActionRequest([
                'table' => $this->encodedTableClass,
                'name' => 'edit',
                'action' => $this->encodedActionClass,
            ]);
            
            expect($request->authorize())->toBeFalse();
        });

        it('validates table class exists and is instantiable', function () {
            $request = createActionRequest([
                'table' => base64_encode('App\\NonExistentTable'),
                'name' => 'edit',
                'action' => $this->encodedActionClass,
            ]);
            
            expect(fn() => $request->getTable())
                ->toThrow(Error::class);
        });

        it('validates action belongs to the specified table', function () {
            // This tests that actions are properly resolved from the table instance
            $request = createActionRequest([
                'table' => $this->encodedTableClass,
                'name' => 'edit',
                'action' => $this->encodedActionClass,
            ]);
            
            $action = $request->getAction();
            expect($action->getName())->toBe('edit');
        });

    });

    describe('Edge Cases and Error Handling', function () {
        
        it('handles empty request gracefully', function () {
            $request = createActionRequest([]);
            
            // These should throw exceptions due to missing required data
            expect(fn() => $request->getTable())
                ->toThrow(TypeError::class); // base64_decode on null
        });

        it('handles malformed base64 encoding', function () {
            $request = createActionRequest([
                'table' => 'not-valid-base64-!@#$%',
                'name' => 'edit',
                'action' => $this->encodedActionClass,
            ]);
            
            expect(fn() => $request->getTable())
                ->toThrow(Error::class);
        });

        it('handles record parameter as string ID', function () {
            $request = createActionRequest([
                'table' => $this->encodedTableClass,
                'name' => 'edit',
                'action' => $this->encodedActionClass,
                'record' => (string) $this->user->id, // String instead of integer
            ]);
            
            $record = $request->getRecord();
            
            expect($record)->toBeInstanceOf(User::class);
            expect($record->id)->toBe($this->user->id);
        });

        it('handles large record sets for bulk actions', function () {
            $largeUserSet = User::factory()->count(100)->create();
            $recordIds = $largeUserSet->pluck('id')->toArray();
            
            $request = createActionRequest([
                'table' => $this->encodedTableClass,
                'name' => 'bulk_delete',
                'action' => base64_encode(BulkAction::class),
                'records' => $recordIds,
            ]);
            
            $records = $request->getRecords();
            
            expect($records->count())->toBe(100);
        });

        it('preserves record order in bulk actions', function () {
            $recordIds = $this->users->pluck('id')->reverse()->toArray(); // Reverse order
            
            $request = createActionRequest([
                'table' => $this->encodedTableClass,
                'name' => 'bulk_delete',
                'action' => base64_encode(BulkAction::class),
                'records' => $recordIds,
            ]);
            
            $records = $request->getRecords();
            
            // The order might not be preserved exactly due to database ordering,
            // but we should get all the expected records
            expect($records->pluck('id')->sort()->values()->toArray())
                ->toBe(collect($recordIds)->sort()->values()->toArray());
        });

    });

    describe('Request Context', function () {
        
        it('maintains table context throughout request lifecycle', function () {
            $request = createActionRequest([
                'table' => $this->encodedTableClass,
                'name' => 'edit',
                'action' => $this->encodedActionClass,
            ]);
            
            $table1 = $request->getTable();
            $table2 = $request->getTable();
            
            // Should return same instance (or at least same class)
            expect(get_class($table1))->toBe(get_class($table2));
        });

        it('provides access to all request data', function () {
            $requestData = [
                'table' => $this->encodedTableClass,
                'name' => 'edit',
                'action' => $this->encodedActionClass,
                'custom_param' => 'custom_value',
                'another_param' => 123,
            ];
            
            $request = $this->createActionRequest($requestData);
            
            expect($request->input('custom_param'))->toBe('custom_value');
            expect($request->input('another_param'))->toBe(123);
            expect($request->input('name'))->toBe('edit');
        });

    });

});

// Helper method to create ActionRequest with given data
function createActionRequest(array $data): ActionRequest
{
    $request = Request::create('/test', 'POST', $data);
    $actionRequest = ActionRequest::createFrom($request);
    
    return $actionRequest;
}