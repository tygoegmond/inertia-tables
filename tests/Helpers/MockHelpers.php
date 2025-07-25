<?php

namespace Egmond\InertiaTables\Tests\Helpers;

use Egmond\InertiaTables\Actions\Action;
use Egmond\InertiaTables\Actions\BulkAction;
use Egmond\InertiaTables\Columns\TextColumn;
use Egmond\InertiaTables\Http\Requests\ActionRequest;
use Illuminate\Http\Request;
use Mockery;

class MockHelpers
{
    /**
     * Create a mock ActionRequest for testing controllers
     */
    public static function createMockActionRequest(array $config): ActionRequest
    {
        $request = Mockery::mock(ActionRequest::class);

        $request->shouldReceive('getTable')->andReturn($config['table'] ?? null);
        $request->shouldReceive('getAction')->andReturn($config['action'] ?? null);
        $request->shouldReceive('getRecord')->andReturn($config['record'] ?? null);
        $request->shouldReceive('getRecords')->andReturn($config['records'] ?? new \Illuminate\Database\Eloquent\Collection([]));
        $request->shouldReceive('expectsJson')->andReturn($config['expects_json'] ?? false);
        $request->shouldReceive('hasHeader')->with('X-Inertia')->andReturn($config['has_inertia_header'] ?? false);
        $request->shouldReceive('header')->with('X-Inertia')->andReturn($config['has_inertia_header'] ? 'true' : null);
        $request->shouldReceive('all')->andReturn($config['all_data'] ?? []);

        // Mock session for messages
        if (isset($config['session_message'])) {
            $request->shouldReceive('session->get')->with('message')->andReturn($config['session_message']);
        } else {
            $request->shouldReceive('session->get')->with('message')->andReturn(null);
        }

        return $request;
    }

    /**
     * Create a mock table for testing
     */
    public static function createMockTable(array $config)
    {
        $table = Mockery::mock();

        if (isset($config['actions'])) {
            $table->shouldReceive('getActions')->andReturn($config['actions']);
        }

        if (isset($config['bulkActions'])) {
            $table->shouldReceive('getBulkActions')->andReturn($config['bulkActions']);
        }

        if (isset($config['columns'])) {
            $table->shouldReceive('getColumns')->andReturn($config['columns']);
        }

        return $table;
    }

    /**
     * Create a mock column for testing
     */
    public static function createMockColumn(array $config): TextColumn
    {
        $column = TextColumn::make($config['key'] ?? 'test_column');

        if (isset($config['label'])) {
            $column->label($config['label']);
        }

        if ($config['sortable'] ?? false) {
            $column->sortable();
        }

        if ($config['searchable'] ?? false) {
            $column->searchable();
        }

        if (isset($config['format'])) {
            $column->format($config['format']);
        }

        if (isset($config['badge'])) {
            $column->badge($config['badge']);
        }

        if (isset($config['limit'])) {
            $column->limit($config['limit']);
        }

        return $column;
    }

    /**
     * Create a mock action for testing
     */
    public static function createMockAction(array $config): Action
    {
        $action = Action::make($config['name'] ?? 'test_action');

        if (isset($config['label'])) {
            $action->label($config['label']);
        }

        if (isset($config['color'])) {
            $action->color($config['color']);
        }

        if (isset($config['authorize'])) {
            $action->authorize($config['authorize']);
        }

        if (isset($config['visible'])) {
            $action->visible($config['visible']);
        }

        if (isset($config['action'])) {
            $action->action($config['action']);
        }

        if (isset($config['confirmation'])) {
            $action->requiresConfirmation($config['confirmation']);
        }

        return $action;
    }

    /**
     * Create a mock bulk action for testing
     */
    public static function createMockBulkAction(array $config): BulkAction
    {
        $bulkAction = BulkAction::make($config['name'] ?? 'test_bulk_action');

        if (isset($config['label'])) {
            $bulkAction->label($config['label']);
        }

        if (isset($config['color'])) {
            $bulkAction->color($config['color']);
        }

        if (isset($config['authorize'])) {
            $bulkAction->authorize($config['authorize']);
        }

        if (isset($config['visible'])) {
            $bulkAction->visible($config['visible']);
        }

        if (isset($config['action'])) {
            $bulkAction->action($config['action']);
        }

        if (isset($config['confirmation'])) {
            $bulkAction->requiresConfirmation($config['confirmation']);
        }

        return $bulkAction;
    }

    /**
     * Create test data structure that mimics database records
     */
    public static function createTestDataStructure(int $count = 5): array
    {
        $data = [];

        for ($i = 1; $i <= $count; $i++) {
            $data[] = [
                'id' => $i,
                'name' => "Test Item {$i}",
                'email' => "test{$i}@example.com",
                'status' => ['active', 'inactive', 'pending'][array_rand(['active', 'inactive', 'pending'])],
                'created_at' => now()->subDays(rand(1, 30))->toISOString(),
                'user' => [
                    'id' => $i,
                    'name' => "User {$i}",
                    'email' => "user{$i}@example.com",
                ],
                'category' => [
                    'id' => ($i % 3) + 1,
                    'name' => 'Category '.(($i % 3) + 1),
                ],
            ];
        }

        return $data;
    }

    /**
     * Create mock request with query parameters
     */
    public static function createMockRequest(array $params = []): Request
    {
        return Request::create('/test', 'GET', $params);
    }

    /**
     * Create mock pagination meta data
     */
    public static function createMockPaginationMeta(array $config = []): array
    {
        return [
            'current_page' => $config['current_page'] ?? 1,
            'total' => $config['total'] ?? 50,
            'per_page' => $config['per_page'] ?? 10,
            'last_page' => $config['last_page'] ?? 5,
            'from' => $config['from'] ?? 1,
            'to' => $config['to'] ?? 10,
        ];
    }

    /**
     * Create mock table configuration
     */
    public static function createMockTableConfig(array $overrides = []): array
    {
        return array_merge([
            'name' => 'test_table',
            'searchable' => true,
            'sortable' => true,
            'paginated' => true,
            'per_page' => 10,
            'default_sort' => 'id',
            'default_direction' => 'asc',
            'search_columns' => ['name', 'email'],
        ], $overrides);
    }

    /**
     * Verify mock expectations were met
     */
    public static function verifyMockExpectations(): void
    {
        Mockery::close();
    }

    /**
     * Create action execution response mock
     */
    public static function createMockActionResponse(array $config = [])
    {
        $response = Mockery::mock();

        $response->shouldReceive('getStatusCode')->andReturn($config['status'] ?? 200);
        $response->shouldReceive('getData')->andReturn($config['data'] ?? ['success' => true]);

        return $response;
    }

    /**
     * Create mock Eloquent model for testing
     */
    public static function createMockModel(array $attributes = [])
    {
        $model = Mockery::mock();

        foreach ($attributes as $key => $value) {
            $model->shouldReceive('getAttribute')->with($key)->andReturn($value);
            $model->{$key} = $value;
        }

        $model->shouldReceive('getKey')->andReturn($attributes['id'] ?? 1);
        $model->shouldReceive('toArray')->andReturn($attributes);

        return $model;
    }

    /**
     * Create mock query builder
     */
    public static function createMockQueryBuilder(array $results = [])
    {
        $builder = Mockery::mock();

        $builder->shouldReceive('get')->andReturn(collect($results));
        $builder->shouldReceive('paginate')->andReturn(new \Illuminate\Pagination\LengthAwarePaginator(
            collect($results),
            count($results),
            10,
            1
        ));
        $builder->shouldReceive('count')->andReturn(count($results));

        return $builder;
    }

    /**
     * Assert mock was called with specific parameters
     */
    public static function assertMockCalledWith($mock, string $method, array $expectedParams): void
    {
        $mock->shouldHaveReceived($method)->with(...$expectedParams);
    }

    /**
     * Create comprehensive test environment setup
     */
    public static function setupTestEnvironment(): array
    {
        $users = collect(range(1, 10))->map(fn ($i) => [
            'id' => $i,
            'name' => "User {$i}",
            'email' => "user{$i}@example.com",
            'status' => ['active', 'inactive'][array_rand(['active', 'inactive'])],
        ]);

        $categories = collect(range(1, 5))->map(fn ($i) => [
            'id' => $i,
            'name' => "Category {$i}",
        ]);

        $posts = collect(range(1, 20))->map(fn ($i) => [
            'id' => $i,
            'title' => "Post {$i}",
            'content' => "Content for post {$i}",
            'status' => ['draft', 'published', 'archived'][array_rand(['draft', 'published', 'archived'])],
            'user_id' => $users->random()['id'],
            'category_id' => $categories->random()['id'],
            'created_at' => now()->subDays(rand(1, 30))->toISOString(),
        ]);

        return [
            'users' => $users->toArray(),
            'categories' => $categories->toArray(),
            'posts' => $posts->toArray(),
        ];
    }
}
