<?php

namespace Egmond\InertiaTables\Tests\Helpers;

use Egmond\InertiaTables\Actions\Action;
use Egmond\InertiaTables\Actions\BulkAction;
use Egmond\InertiaTables\Columns\TextColumn;
use Egmond\InertiaTables\Table;
use Egmond\InertiaTables\Tests\Database\Models\User;
use Egmond\InertiaTables\Tests\Database\Models\Post;
use Egmond\InertiaTables\Tests\Database\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

trait TableTestTrait
{
    /**
     * Create a simple test table for basic functionality testing
     */
    protected function createSimpleTable(?string $model = null): Table
    {
        $model = $model ?? User::class;
        
        return new class($model) extends Table {
            private string $model;
            
            public function __construct(string $model)
            {
                $this->model = $model;
            }
            
            public function build(): \Egmond\InertiaTables\TableResult
            {
                return $this->query($this->model::query())
                    ->as('simple_table')
                    ->columns([
                        TextColumn::make('name')->sortable()->searchable(),
                        TextColumn::make('email')->sortable()->searchable(),
                    ])
                    ->actions([
                        Action::make('edit')->authorize(fn() => true),
                        Action::make('delete')->authorize(fn() => true),
                    ])
                    ->setTableClass(get_class($this))
                    ->build();
            }
        };
    }
    
    /**
     * Create a comprehensive test table with all features
     */
    protected function createComprehensiveTable(): Table
    {
        return new class extends Table {
            public function build(): \Egmond\InertiaTables\TableResult
            {
                return $this->query(Post::with(['user', 'category']))
                    ->as('comprehensive_table')
                    ->columns([
                        TextColumn::make('title')
                            ->sortable()
                            ->searchable()
                            ->limit(50),
                        TextColumn::make('user.name')
                            ->sortable()
                            ->searchable()
                            ->label('Author'),
                        TextColumn::make('category.name')
                            ->sortable()
                            ->label('Category'),
                        TextColumn::make('status')
                            ->sortable()
                            ->badge(fn($value) => match($value) {
                                'published' => 'success',
                                'draft' => 'warning',
                                'archived' => 'secondary',
                                default => 'primary'
                            }),
                        TextColumn::make('created_at')
                            ->sortable()
                            ->format(fn($value) => $value->format('M j, Y')),
                    ])
                    ->actions([
                        Action::make('edit')
                            ->authorize(fn($record) => $record->status !== 'archived'),
                        Action::make('publish')
                            ->authorize(fn($record) => $record->status === 'draft'),
                        Action::make('archive')
                            ->color('danger')
                            ->authorize(fn($record) => $record->status !== 'archived'),
                    ])
                    ->bulkActions([
                        BulkAction::make('bulk_publish')
                            ->authorize(fn() => true),
                        BulkAction::make('bulk_archive')
                            ->authorize(fn() => true),
                    ])
                    ->defaultSort('created_at', 'desc')
                    ->perPage(10)
                    ->searchColumns(['title', 'user.name', 'category.name'])
                    ->setTableClass(get_class($this))
                    ->build();
            }
        };
    }
    
    /**
     * Create test data with relationships
     */
    protected function createTestData(int $userCount = 5, int $categoryCount = 3, int $postCount = 15): array
    {
        $categories = Category::factory()->count($categoryCount)->create();
        $users = User::factory()->count($userCount)->create();
        
        $posts = Post::factory()->count($postCount)->create([
            'user_id' => fn() => $users->random()->id,
            'category_id' => fn() => $categories->random()->id,
        ]);
        
        return [
            'categories' => $categories,
            'users' => $users,
            'posts' => $posts,
        ];
    }
    
    /**
     * Create a test request with common parameters
     */
    protected function createTestRequest(array $params = []): Request
    {
        $defaultParams = [
            'search' => '',
            'sort' => 'id',
            'direction' => 'asc',
            'page' => 1,
            'per_page' => 10,
        ];
        
        $params = array_merge($defaultParams, $params);
        
        return Request::create('/test', 'GET', $params);
    }
    
    /**
     * Generate signed URL for action execution
     */
    protected function generateActionUrl(string $tableClass, string $actionName, string $actionClass, ?int $recordId = null): string
    {
        $params = [
            'table' => base64_encode($tableClass),
            'name' => $actionName,
            'action' => base64_encode($actionClass),
        ];
        
        if ($recordId) {
            $params['record'] = $recordId;
        }
        
        return URL::signedRoute('inertia-tables.action', $params);
    }
    
    /**
     * Execute a regular action via HTTP
     */
    protected function executeAction(string $tableClass, string $actionName, int $recordId, array $additionalData = []): \Illuminate\Testing\TestResponse
    {
        $url = $this->generateActionUrl($tableClass, $actionName, Action::class, $recordId);
        
        $data = array_merge([
            'table' => base64_encode($tableClass),
            'name' => $actionName,
            'action' => base64_encode(Action::class),
            'record' => $recordId,
        ], $additionalData);
        
        return $this->post($url, $data);
    }
    
    /**
     * Execute a bulk action via HTTP
     */
    protected function executeBulkAction(string $tableClass, string $actionName, array $recordIds, array $additionalData = []): \Illuminate\Testing\TestResponse
    {
        $url = $this->generateActionUrl($tableClass, $actionName, BulkAction::class);
        
        $data = array_merge([
            'table' => base64_encode($tableClass),
            'name' => $actionName,
            'action' => base64_encode(BulkAction::class),
            'records' => $recordIds,
        ], $additionalData);
        
        return $this->post($url, $data);
    }
    
    /**
     * Assert table result structure
     */
    protected function assertTableResultStructure(array $result): void
    {
        expect($result)->toHaveKeys(['data', 'meta', 'columns', 'actions', 'bulkActions', 'config']);
        expect($result['data'])->toBeArray();
        expect($result['meta'])->toHaveKeys(['current_page', 'total', 'per_page']);
        expect($result['columns'])->toBeArray();
        expect($result['actions'])->toBeArray();
        expect($result['bulkActions'])->toBeArray();
        expect($result['config'])->toBeArray();
    }
    
    /**
     * Assert column structure
     */
    protected function assertColumnStructure(array $column): void
    {
        expect($column)->toHaveKeys(['key', 'label', 'sortable', 'searchable']);
        expect($column['key'])->toBeString();
        expect($column['label'])->toBeString();
        expect($column['sortable'])->toBeBool();
        expect($column['searchable'])->toBeBool();
    }
    
    /**
     * Assert action structure
     */
    protected function assertActionStructure(array $action): void
    {
        expect($action)->toHaveKeys(['name', 'label', 'color']);
        expect($action['name'])->toBeString();
        expect($action['label'])->toBeString();
        expect($action['color'])->toBeString();
    }
    
    /**
     * Assert bulk action structure
     */
    protected function assertBulkActionStructure(array $bulkAction): void
    {
        expect($bulkAction)->toHaveKeys(['name', 'label', 'color']);
        expect($bulkAction['name'])->toBeString();
        expect($bulkAction['label'])->toBeString();
        expect($bulkAction['color'])->toBeString();
    }
    
    /**
     * Assert metadata structure
     */
    protected function assertMetaStructure(array $meta): void
    {
        expect($meta)->toHaveKeys(['current_page', 'total', 'per_page', 'last_page']);
        expect($meta['current_page'])->toBeInt();
        expect($meta['total'])->toBeInt();
        expect($meta['per_page'])->toBeInt();
        expect($meta['last_page'])->toBeInt();
    }
    
    /**
     * Find a record in table data by ID
     */
    protected function findRecordInTableData(array $tableData, int $recordId): ?array
    {
        foreach ($tableData as $record) {
            if (isset($record['id']) && $record['id'] === $recordId) {
                return $record;
            }
        }
        
        return null;
    }
    
    /**
     * Find an action in record actions by name
     */
    protected function findActionInRecord(array $record, string $actionName): ?array
    {
        if (!isset($record['actions'])) {
            return null;
        }
        
        foreach ($record['actions'] as $action) {
            if ($action['name'] === $actionName) {
                return $action;
            }
        }
        
        return null;
    }
    
    /**
     * Assert sorting is applied correctly (ascending)
     */
    protected function assertSortingAsc(array $data, string $key): void
    {
        if (count($data) < 2) {
            return; // Not enough data to test sorting
        }
        
        for ($i = 1; $i < count($data); $i++) {
            $prev = $this->getNestedValue($data[$i-1], $key);
            $current = $this->getNestedValue($data[$i], $key);
            
            expect($prev <= $current)->toBeTrue(
                "Sorting failed: {$prev} should be <= {$current} for key {$key}"
            );
        }
    }
    
    /**
     * Assert sorting is applied correctly (descending)
     */
    protected function assertSortingDesc(array $data, string $key): void
    {
        if (count($data) < 2) {
            return; // Not enough data to test sorting
        }
        
        for ($i = 1; $i < count($data); $i++) {
            $prev = $this->getNestedValue($data[$i-1], $key);
            $current = $this->getNestedValue($data[$i], $key);
            
            expect($prev >= $current)->toBeTrue(
                "Sorting failed: {$prev} should be >= {$current} for key {$key}"
            );
        }
    }
    
    /**
     * Get nested value from array using dot notation
     */
    protected function getNestedValue(array $array, string $key)
    {
        $keys = explode('.', $key);
        $value = $array;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return null;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
    
    /**
     * Assert search functionality works
     */
    protected function assertSearchWorks(array $data, string $searchTerm, array $searchableFields): void
    {
        if (empty($data)) {
            return; // No data to search
        }
        
        $found = false;
        foreach ($data as $record) {
            foreach ($searchableFields as $field) {
                $value = $this->getNestedValue($record, $field);
                if ($value && stripos($value, $searchTerm) !== false) {
                    $found = true;
                    break 2;
                }
            }
        }
        
        expect($found)->toBeTrue("Search term '{$searchTerm}' not found in any searchable fields");
    }
    
    /**
     * Create test posts with specific statuses
     */
    protected function createPostsWithStatuses(array $statuses, int $countPerStatus = 2): array
    {
        $posts = [];
        
        foreach ($statuses as $status) {
            $statusPosts = Post::factory()->count($countPerStatus)->create([
                'status' => $status,
                'user_id' => User::factory(),
                'category_id' => Category::factory(),
            ]);
            
            $posts[$status] = $statusPosts;
        }
        
        return $posts;
    }
    
    /**
     * Assert action execution was successful
     */
    protected function assertActionExecuted(\Illuminate\Testing\TestResponse $response, int $expectedStatus = 302): void
    {
        $response->assertStatus($expectedStatus);
        
        if ($expectedStatus === 302) {
            $response->assertRedirect();
        }
    }
    
    /**
     * Assert JSON action response structure
     */
    protected function assertJsonActionResponse(\Illuminate\Testing\TestResponse $response): void
    {
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['success', 'redirect', 'message']);
    }
}