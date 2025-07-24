<?php

use Egmond\InertiaTables\Actions\Action;
use Egmond\InertiaTables\Actions\BulkAction;
use Egmond\InertiaTables\Http\Controllers\ActionController;
use Egmond\InertiaTables\Http\Requests\ActionRequest;
use Egmond\InertiaTables\Tests\Database\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;
use Illuminate\Routing\UrlGenerator;
use Egmond\InertiaTables\Tests\Helpers\MockHelpers;
use Mockery;

describe('ActionController Class', function () {

    beforeEach(function () {
        $this->controller = new ActionController;
        $this->user = User::factory()->create(['name' => 'Test User']);
        
        // Mock URL generator globally for all tests
        $urlGenerator = Mockery::mock(UrlGenerator::class);
        $urlGenerator->shouldReceive('previous')->andReturn('/previous-url');
        $urlGenerator->shouldReceive('getRequest')->andReturn(Mockery::mock(\Illuminate\Http\Request::class));
        URL::swap($urlGenerator);
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
                ->action(function ($record) {
                    return 'Action executed for user: '.$record->name;
                });

            $this->table = MockHelpers::createMockTable([
                'actions' => [$this->action],
            ]);
        });

        it('executes regular action with valid request', function () {
            $request = MockHelpers::createMockActionRequest([
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

        it('returns redirect response for non-JSON requests', function () {
            $request = MockHelpers::createMockActionRequest([
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

        it('returns json response for JSON requests', function () {
            $request = MockHelpers::createMockActionRequest([
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
        });

        it('skips action execution when not authorized', function () {
            $unauthorizedAction = Action::make('delete')
                ->authorize(fn () => false)
                ->action(function ($record) {
                    return 'Should not execute';
                });

            $table = MockHelpers::createMockTable([
                'actions' => [$unauthorizedAction],
            ]);

            $request = MockHelpers::createMockActionRequest([
                'action_type' => 'regular',
                'table' => $table,
                'action' => $unauthorizedAction,
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
            $this->bulkAction = BulkAction::make('bulk_delete')
                ->authorize(fn () => true)
                ->action(function ($records) {
                    return 'Bulk action executed on '.count($records).' records';
                });

            $this->table = MockHelpers::createMockTable([
                'bulkActions' => [$this->bulkAction],
            ]);
        });

        it('executes bulk action with valid request', function () {
            $request = MockHelpers::createMockActionRequest([
                'action_type' => 'bulk',
                'table' => $this->table,
                'action' => $this->bulkAction,
                'record' => null,
                'records' => $this->users,
                'expects_json' => false,
                'has_inertia_header' => false,
            ]);


            $response = $this->controller->__invoke($request);

            expect($response)->toBeInstanceOf(RedirectResponse::class);
        });

        it('returns json response for bulk actions', function () {
            $request = MockHelpers::createMockActionRequest([
                'action_type' => 'bulk',
                'table' => $this->table,
                'action' => $this->bulkAction,
                'record' => null,
                'records' => $this->users,
                'expects_json' => true,
                'has_inertia_header' => false,
            ]);

            $response = $this->controller->__invoke($request);

            expect($response)->toBeInstanceOf(JsonResponse::class);
        });

        it('handles empty record sets', function () {
            $request = MockHelpers::createMockActionRequest([
                'action_type' => 'bulk',
                'table' => $this->table,
                'action' => $this->bulkAction,
                'record' => null,
                'records' => new \Illuminate\Database\Eloquent\Collection([]),
                'expects_json' => false,
                'has_inertia_header' => false,
            ]);


            $response = $this->controller->__invoke($request);

            expect($response)->toBeInstanceOf(RedirectResponse::class);
        });

    });

    describe('Response Handling', function () {

        beforeEach(function () {
            $this->action = Action::make('edit')
                ->authorize(fn () => true)
                ->action(function ($record) {
                    return 'Action executed';
                });

            $this->table = MockHelpers::createMockTable([
                'actions' => [$this->action],
            ]);
        });

        it('includes redirect url in response', function () {
            $request = MockHelpers::createMockActionRequest([
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
            expect($data)->toHaveKey('redirect_url');
        });

        it('handles redirect responses', function () {
            $request = MockHelpers::createMockActionRequest([
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

        it('includes session messages', function () {
            $request = MockHelpers::createMockActionRequest([
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
            expect($data)->toHaveKey('message');
        });

    });

    describe('Action Authorization', function () {

        it('processes authorized actions', function () {
            $action = Action::make('edit')
                ->authorize(fn () => true)
                ->action(function ($record) {
                    return 'Authorized action executed';
                });

            $table = MockHelpers::createMockTable([
                'actions' => [$action],
            ]);

            $request = MockHelpers::createMockActionRequest([
                'action_type' => 'regular',
                'table' => $table,
                'action' => $action,
                'record' => $this->user,
                'records' => null,
                'expects_json' => true,
                'has_inertia_header' => false,
            ]);

            $response = $this->controller->__invoke($request);

            expect($response)->toBeInstanceOf(JsonResponse::class);
        });

        it('handles bulk action authorization', function () {
            $users = User::factory()->count(2)->create();
            $bulkAction = BulkAction::make('bulk_edit')
                ->authorize(fn () => true)
                ->action(function ($records) {
                    return 'Bulk authorized action executed';
                });

            $table = MockHelpers::createMockTable([
                'bulkActions' => [$bulkAction],
            ]);

            $request = MockHelpers::createMockActionRequest([
                'action_type' => 'bulk',
                'table' => $table,
                'action' => $bulkAction,
                'record' => null,
                'records' => $users,
                'expects_json' => true,
                'has_inertia_header' => false,
            ]);

            $response = $this->controller->__invoke($request);

            expect($response)->toBeInstanceOf(JsonResponse::class);
        });

    });

    describe('Edge Cases and Error Handling', function () {

        it('handles action execution with parameters', function () {
            $actionWithParams = Action::make('update')
                ->authorize(fn () => true)
                ->action(function ($record) {
                    return 'Updated record: '.$record->name;
                });

            $request = MockHelpers::createMockActionRequest([
                'action_type' => 'regular',
                'table' => MockHelpers::createMockTable(['actions' => [$actionWithParams]]),
                'action' => $actionWithParams,
                'record' => $this->user,
                'records' => null,
                'expects_json' => true,
                'has_inertia_header' => false,
            ]);

            $response = $this->controller->__invoke($request);

            expect($response)->toBeInstanceOf(JsonResponse::class);
        });

        it('distinguishes between regular and bulk actions', function () {
            $regularAction = Action::make('edit')->authorize(fn () => true)->action(fn ($record) => 'regular');
            $bulkAction = BulkAction::make('delete')->authorize(fn () => true)->action(fn ($records) => 'bulk');

            // Test regular action
            $regularRequest = MockHelpers::createMockActionRequest([
                'action_type' => 'regular',
                'table' => MockHelpers::createMockTable(['actions' => [$regularAction]]),
                'action' => $regularAction,
                'record' => $this->user,
                'records' => null,
                'expects_json' => false,
                'has_inertia_header' => false,
            ]);


            $response1 = $this->controller->__invoke($regularRequest);
            expect($response1)->toBeInstanceOf(RedirectResponse::class);

            // Test bulk action
            $bulkRequest = MockHelpers::createMockActionRequest([
                'action_type' => 'bulk',
                'table' => MockHelpers::createMockTable(['bulkActions' => [$bulkAction]]),
                'action' => $bulkAction,
                'record' => null,
                'records' => User::factory()->count(2)->create(),
                'expects_json' => false,
                'has_inertia_header' => false,
            ]);

            $response2 = $this->controller->__invoke($bulkRequest);
            expect($response2)->toBeInstanceOf(RedirectResponse::class);
        });

    });

});