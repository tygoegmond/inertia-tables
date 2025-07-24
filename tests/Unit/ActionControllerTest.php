<?php

use Egmond\InertiaTables\Actions\Action;
use Egmond\InertiaTables\Actions\BulkAction;
use Egmond\InertiaTables\Http\Controllers\ActionController;
use Egmond\InertiaTables\Http\Requests\ActionRequest;
use Egmond\InertiaTables\Tests\Database\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;

describe('ActionController Class', function () {

    beforeEach(function () {
        $this->controller = new ActionController;
        $this->user = User::factory()->create(['name' => 'Test User']);
    });

    describe('Controller Invocation', function () {

        it('is an invokable controller', function () {
            expect(method_exists($this->controller, '__invoke'))->toBeTrue();
        });

        it('accepts ActionRequest parameter', function () {
            $reflection = new ReflectionMethod(ActionController::class, '__invoke');
            $parameters = $reflection->getParameters();

            expect(count($parameters))->toBe(1);
            expect($parameters[0]->getType()->getName())->toBe(ActionRequest::class);
        });

    });

    describe('Regular Action Execution', function () {

        beforeEach(function () {
            $this->action = Action::make('edit')
                ->authorize(fn () => true)
                ->action(function ($record, $params) {
                    return 'Action executed for user: '.$record->name;
                });

            $this->table = createMockTable([
                'actions' => [$this->action],
            ]);
        });

        it('executes regular action with valid request', function () {
            $request = createMockActionRequest([
                'action_type' => 'regular',
                'table' => $this->table,
                'action' => $this->action,
                'record' => $this->user,
                'records' => null,
                'expects_json' => false,
                'has_inertia_header' => false,
            ]);

            $response = $this->controller->__invoke($request);

            expect($response)->toBeInstanceOf(RedirectResponse::class);
        });

        it('returns json response when expecting json without inertia header', function () {
            $request = createMockActionRequest([
                'action_type' => 'regular',
                'table' => $this->table,
                'action' => $this->action,
                'record' => $this->user,
                'records' => null,
                'expects_json' => true,
                'has_inertia_header' => false,
            ]);

            $response = $this->controller->__invoke($request);

            expect($response)->toBeInstanceOf(JsonResponse::class);

            $data = $response->getData(true);
            expect($data)->toHaveKeys(['success', 'redirect', 'message']);
            expect($data['success'])->toBeTrue();
        });

        it('returns redirect response when has inertia header', function () {
            $request = createMockActionRequest([
                'action_type' => 'regular',
                'table' => $this->table,
                'action' => $this->action,
                'record' => $this->user,
                'records' => null,
                'expects_json' => true,
                'has_inertia_header' => true,
            ]);

            $response = $this->controller->__invoke($request);

            expect($response)->toBeInstanceOf(RedirectResponse::class);
        });

        it('skips action execution when action has no logic', function () {
            $actionWithoutLogic = Action::make('view')
                ->authorize(fn () => true);
            // No ->action() call means hasAction() returns false

            $request = createMockActionRequest([
                'action_type' => 'regular',
                'table' => createMockTable(['actions' => [$actionWithoutLogic]]),
                'action' => $actionWithoutLogic,
                'record' => $this->user,
                'records' => null,
                'expects_json' => false,
                'has_inertia_header' => false,
            ]);

            $response = $this->controller->__invoke($request);

            expect($response)->toBeInstanceOf(RedirectResponse::class);
        });

    });

    describe('Bulk Action Execution', function () {

        beforeEach(function () {
            $this->users = User::factory()->count(3)->create();

            $this->bulkAction = BulkAction::make('delete')
                ->authorize(fn () => true)
                ->action(function ($records, $params) {
                    return 'Bulk action executed for '.count($records).' records';
                });

            $this->table = createMockTable([
                'bulkActions' => [$this->bulkAction],
            ]);
        });

        it('executes bulk action with multiple records', function () {
            $request = createMockActionRequest([
                'action_type' => 'bulk',
                'table' => $this->table,
                'action' => $this->bulkAction,
                'record' => null,
                'records' => $this->users->toArray(),
                'expects_json' => false,
                'has_inertia_header' => false,
            ]);

            $response = $this->controller->__invoke($request);

            expect($response)->toBeInstanceOf(RedirectResponse::class);
        });

        it('returns json response for bulk action when expecting json', function () {
            $request = createMockActionRequest([
                'action_type' => 'bulk',
                'table' => $this->table,
                'action' => $this->bulkAction,
                'record' => null,
                'records' => $this->users->toArray(),
                'expects_json' => true,
                'has_inertia_header' => false,
            ]);

            $response = $this->controller->__invoke($request);

            expect($response)->toBeInstanceOf(JsonResponse::class);

            $data = $response->getData(true);
            expect($data['success'])->toBeTrue();
        });

        it('handles empty records array for bulk action', function () {
            $request = createMockActionRequest([
                'action_type' => 'bulk',
                'table' => $this->table,
                'action' => $this->bulkAction,
                'record' => null,
                'records' => [],
                'expects_json' => false,
                'has_inertia_header' => false,
            ]);

            $response = $this->controller->__invoke($request);

            expect($response)->toBeInstanceOf(RedirectResponse::class);
        });

    });

    describe('Response Handling', function () {

        beforeEach(function () {
            $this->action = Action::make('test')
                ->authorize(fn () => true)
                ->action(fn ($record) => 'Test result');

            $this->table = createMockTable([
                'actions' => [$this->action],
            ]);
        });

        it('includes redirect url in json response', function () {
            $request = createMockActionRequest([
                'action_type' => 'regular',
                'table' => $this->table,
                'action' => $this->action,
                'record' => $this->user,
                'records' => null,
                'expects_json' => true,
                'has_inertia_header' => false,
                'intended_url' => 'http://example.com/redirect',
            ]);

            $response = $this->controller->__invoke($request);

            expect($response)->toBeInstanceOf(JsonResponse::class);

            $data = $response->getData(true);
            expect($data['redirect'])->toBe('http://example.com/redirect');
        });

        it('includes session message in json response', function () {
            $request = createMockActionRequest([
                'action_type' => 'regular',
                'table' => $this->table,
                'action' => $this->action,
                'record' => $this->user,
                'records' => null,
                'expects_json' => true,
                'has_inertia_header' => false,
                'session_message' => 'Action completed successfully',
            ]);

            $response = $this->controller->__invoke($request);

            expect($response)->toBeInstanceOf(JsonResponse::class);

            $data = $response->getData(true);
            expect($data['message'])->toBe('Action completed successfully');
        });

        it('handles redirect response properly', function () {
            $request = createMockActionRequest([
                'action_type' => 'regular',
                'table' => $this->table,
                'action' => $this->action,
                'record' => $this->user,
                'records' => null,
                'expects_json' => false,
                'has_inertia_header' => false,
                'intended_url' => 'http://example.com/redirect',
            ]);

            $response = $this->controller->__invoke($request);

            expect($response)->toBeInstanceOf(RedirectResponse::class);
            expect($response->getTargetUrl())->toBe('http://example.com/redirect');
        });

    });

    describe('Action Authorization', function () {

        it('processes authorized actions', function () {
            $authorizedAction = Action::make('edit')
                ->authorize(fn () => true)
                ->action(fn ($record) => 'Authorized action executed');

            $request = createMockActionRequest([
                'action_type' => 'regular',
                'table' => createMockTable(['actions' => [$authorizedAction]]),
                'action' => $authorizedAction,
                'record' => $this->user,
                'records' => null,
                'expects_json' => false,
                'has_inertia_header' => false,
            ]);

            $response = $this->controller->__invoke($request);

            expect($response)->toBeInstanceOf(RedirectResponse::class);
        });

        it('handles bulk actions with authorization', function () {
            $authorizedBulkAction = BulkAction::make('archive')
                ->authorize(fn () => true)
                ->action(fn ($records) => 'Bulk action executed');

            $request = createMockActionRequest([
                'action_type' => 'bulk',
                'table' => createMockTable(['bulkActions' => [$authorizedBulkAction]]),
                'action' => $authorizedBulkAction,
                'record' => null,
                'records' => $this->users->toArray(),
                'expects_json' => false,
                'has_inertia_header' => false,
            ]);

            $response = $this->controller->__invoke($request);

            expect($response)->toBeInstanceOf(RedirectResponse::class);
        });

    });

    describe('Edge Cases and Error Handling', function () {

        it('handles action execution with parameters', function () {
            $actionWithParams = Action::make('update')
                ->authorize(fn () => true)
                ->action(function ($record, $params) {
                    return 'Updated with params: '.json_encode($params);
                });

            $request = createMockActionRequest([
                'action_type' => 'regular',
                'table' => createMockTable(['actions' => [$actionWithParams]]),
                'action' => $actionWithParams,
                'record' => $this->user,
                'records' => null,
                'expects_json' => false,
                'has_inertia_header' => false,
                'all_data' => ['param1' => 'value1', 'param2' => 'value2'],
            ]);

            $response = $this->controller->__invoke($request);

            expect($response)->toBeInstanceOf(RedirectResponse::class);
        });

        it('distinguishes between regular and bulk actions correctly', function () {
            $regularAction = Action::make('edit')->authorize(fn () => true)->action(fn ($record) => 'regular');
            $bulkAction = BulkAction::make('delete')->authorize(fn () => true)->action(fn ($records) => 'bulk');

            // Test regular action
            $regularRequest = $this->createMockActionRequest([
                'action_type' => 'regular',
                'table' => createMockTable(['actions' => [$regularAction]]),
                'action' => $regularAction,
                'record' => $this->user,
                'records' => null,
                'expects_json' => false,
                'has_inertia_header' => false,
            ]);

            $response1 = $this->controller->__invoke($regularRequest);
            expect($response1)->toBeInstanceOf(RedirectResponse::class);

            // Test bulk action
            $bulkRequest = $this->createMockActionRequest([
                'action_type' => 'bulk',
                'table' => createMockTable(['bulkActions' => [$bulkAction]]),
                'action' => $bulkAction,
                'record' => null,
                'records' => User::factory()->count(2)->create()->toArray(),
                'expects_json' => false,
                'has_inertia_header' => false,
            ]);

            $response2 = $this->controller->__invoke($bulkRequest);
            expect($response2)->toBeInstanceOf(RedirectResponse::class);
        });

    });

});

// Helper method to create mock ActionRequest
function createMockActionRequest(array $config): ActionRequest
{
    $request = Mockery::mock(ActionRequest::class);

    $request->shouldReceive('getTable')->andReturn($config['table']);
    $request->shouldReceive('getAction')->andReturn($config['action']);

    if ($config['action_type'] === 'regular') {
        $request->shouldReceive('getRecord')->andReturn($config['record']);
        $request->shouldReceive('getRecords')->andReturn($config['records']);
    } else {
        $request->shouldReceive('getRecord')->andReturn($config['record']);
        $request->shouldReceive('getRecords')->andReturn($config['records']);
    }

    $request->shouldReceive('expectsJson')->andReturn($config['expects_json'] ?? false);
    $request->shouldReceive('hasHeader')->with('X-Inertia')->andReturn($config['has_inertia_header'] ?? false);
    $request->shouldReceive('all')->andReturn($config['all_data'] ?? []);

    // Mock redirect URL
    URL::shouldReceive('intended')->andReturn($config['intended_url'] ?? 'http://localhost');

    // Mock session message
    if (isset($config['session_message'])) {
        $request->shouldReceive('session->get')->with('message')->andReturn($config['session_message']);
    } else {
        $request->shouldReceive('session->get')->with('message')->andReturn(null);
    }

    return $request;
}

// Helper method to create mock table
function createMockTable(array $config)
{
    $table = Mockery::mock();

    if (isset($config['actions'])) {
        $table->shouldReceive('getActions')->andReturn($config['actions']);
    }

    if (isset($config['bulkActions'])) {
        $table->shouldReceive('getBulkActions')->andReturn($config['bulkActions']);
    }

    return $table;
}
