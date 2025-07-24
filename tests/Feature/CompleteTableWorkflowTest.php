<?php

use Egmond\InertiaTables\Actions\Action;
use Egmond\InertiaTables\Actions\BulkAction;
use Egmond\InertiaTables\Columns\TextColumn;
use Egmond\InertiaTables\Facades\InertiaTables;
use Egmond\InertiaTables\Table;
use Egmond\InertiaTables\Tests\Database\Models\Category;
use Egmond\InertiaTables\Tests\Database\Models\Post;
use Egmond\InertiaTables\Tests\Database\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

describe('Complete Table Workflow Feature Tests', function () {

    beforeEach(function () {
        // Create test data with relationships
        $this->categories = Category::factory()->count(5)->create();
        $this->users = User::factory()->count(10)->create();

        $this->posts = Post::factory()->count(20)->create([
            'user_id' => fn () => $this->users->random()->id,
            'category_id' => fn () => $this->categories->random()->id,
        ]);

        // Create comprehensive table class for testing
        $this->tableClass = new class extends Table
        {
            public function build(): \Egmond\InertiaTables\TableResult
            {
                return $this->query(Post::with(['user', 'category']))
                    ->as('posts')
                    ->columns([
                        TextColumn::make('title')
                            ->sortable()
                            ->searchable(),
                        TextColumn::make('user.name')
                            ->sortable()
                            ->searchable()
                            ->label('Author'),
                        TextColumn::make('category.name')
                            ->sortable()
                            ->label('Category'),
                        TextColumn::make('status')
                            ->sortable()
                            ->badge(fn ($value) => match ($value) {
                                'published' => 'success',
                                'draft' => 'warning',
                                'archived' => 'secondary',
                                default => 'primary'
                            }),
                        TextColumn::make('created_at')
                            ->sortable()
                            ->format(fn ($value) => $value->format('M j, Y')),
                    ])
                    ->actions([
                        Action::make('edit')
                            ->authorize(fn ($record) => $record->status !== 'archived')
                            ->action(function ($record, $params) {
                                $record->update(['status' => 'draft']);

                                return 'Post updated to draft';
                            }),
                        Action::make('publish')
                            ->authorize(fn ($record) => $record->status === 'draft')
                            ->action(function ($record, $params) {
                                $record->update(['status' => 'published']);

                                return 'Post published successfully';
                            }),
                        Action::make('archive')
                            ->color('danger')
                            ->authorize(fn ($record) => $record->status !== 'archived')
                            ->action(function ($record, $params) {
                                $record->update(['status' => 'archived']);

                                return 'Post archived successfully';
                            }),
                    ])
                    ->bulkActions([
                        BulkAction::make('bulk_publish')
                            ->authorize(fn () => true)
                            ->action(function ($records, $params) {
                                $count = 0;
                                foreach ($records as $record) {
                                    if ($record->status === 'draft') {
                                        $record->update(['status' => 'published']);
                                        $count++;
                                    }
                                }

                                return "Published {$count} posts";
                            }),
                        BulkAction::make('bulk_archive')
                            ->authorize(fn () => true)
                            ->action(function ($records, $params) {
                                $count = 0;
                                foreach ($records as $record) {
                                    if ($record->status !== 'archived') {
                                        $record->update(['status' => 'archived']);
                                        $count++;
                                    }
                                }

                                return "Archived {$count} posts";
                            }),
                    ])
                    ->defaultSort('created_at', 'desc')
                    ->perPage(10)
                    ->searchColumns(['title', 'user.name', 'category.name'])
                    ->setTableClass(get_class($this))
                    ->build();
            }
        };
    });

    describe('Complete Table Building and Rendering', function () {

        it('can build complete table with all features', function () {
            $request = Request::create('/test', 'GET', [
                'search' => 'test',
                'sort' => 'title',
                'direction' => 'asc',
                'page' => 1,
                'per_page' => 5,
            ]);

            $result = InertiaTables::table($request)
                ->using($this->tableClass)
                ->build();

            expect($result)->toBeInstanceOf(\Egmond\InertiaTables\TableResult::class);

            $array = $result->toArray();

            // Test core structure
            expect($array)->toHaveKeys(['data', 'meta', 'columns', 'actions', 'bulkActions', 'config']);

            // Test data structure
            expect($array['data'])->toBeArray();
            expect(count($array['data']))->toBeLessThanOrEqual(5); // Respects per_page

            // Test metadata
            expect($array['meta'])->toHaveKeys(['current_page', 'total', 'per_page']);
            expect($array['meta']['per_page'])->toBe(5);

            // Test columns
            expect($array['columns'])->toBeArray();
            expect(count($array['columns']))->toBe(5);

            // Test actions
            expect($array['actions'])->toBeArray();
            expect(count($array['actions']))->toBe(3);

            // Test bulk actions
            expect($array['bulkActions'])->toBeArray();
            expect(count($array['bulkActions']))->toBe(2);
        });

        it('handles complex search across relationships', function () {
            $user = $this->users->first();
            $request = Request::create('/test', 'GET', [
                'search' => $user->name,
            ]);

            $result = InertiaTables::table($request)
                ->using($this->tableClass)
                ->build();

            $array = $result->toArray();

            expect($array['data'])->toBeArray();

            // Should find posts by this user
            $foundPost = false;
            foreach ($array['data'] as $post) {
                if ($post['user']['name'] === $user->name) {
                    $foundPost = true;
                    break;
                }
            }
            expect($foundPost)->toBeTrue();
        });

        it('applies sorting correctly across relationships', function () {
            $request = Request::create('/test', 'GET', [
                'sort' => 'user.name',
                'direction' => 'asc',
            ]);

            $result = InertiaTables::table($request)
                ->using($this->tableClass)
                ->build();

            $array = $result->toArray();

            expect($array['data'])->toBeArray();
            expect(count($array['data']))->toBeGreaterThan(1);

            // Verify sorting by checking if first user name is <= second user name
            if (count($array['data']) >= 2) {
                $firstName = $array['data'][0]['user']['name'];
                $secondName = $array['data'][1]['user']['name'];
                expect($firstName <= $secondName)->toBeTrue();
            }
        });

        it('handles pagination correctly', function () {
            $request = Request::create('/test', 'GET', [
                'page' => 2,
                'per_page' => 5,
            ]);

            $result = InertiaTables::table($request)
                ->using($this->tableClass)
                ->build();

            $array = $result->toArray();

            expect($array['meta']['current_page'])->toBe(2);
            expect($array['meta']['per_page'])->toBe(5);
            expect(count($array['data']))->toBeLessThanOrEqual(5);
        });

    });

    describe('End-to-End Action Execution', function () {

        it('can execute regular action through complete workflow', function () {
            $post = $this->posts->where('status', 'published')->first();

            $signedUrl = URL::signedRoute('inertia-tables.action', [
                'table' => base64_encode(get_class($this->tableClass)),
                'name' => 'archive',
                'action' => base64_encode(Action::class),
                'record' => $post->id,
            ]);

            $response = $this->post($signedUrl, [
                'table' => base64_encode(get_class($this->tableClass)),
                'name' => 'archive',
                'action' => base64_encode(Action::class),
                'record' => $post->id,
            ]);

            $response->assertStatus(302);

            // Verify the action was executed
            $post->refresh();
            expect($post->status)->toBe('archived');
        });

        it('can execute bulk action through complete workflow', function () {
            $draftPosts = $this->posts->where('status', 'draft')->take(3);
            $postIds = $draftPosts->pluck('id')->toArray();

            $signedUrl = URL::signedRoute('inertia-tables.action', [
                'table' => base64_encode(get_class($this->tableClass)),
                'name' => 'bulk_publish',
                'action' => base64_encode(BulkAction::class),
            ]);

            $response = $this->post($signedUrl, [
                'table' => base64_encode(get_class($this->tableClass)),
                'name' => 'bulk_publish',
                'action' => base64_encode(BulkAction::class),
                'records' => $postIds,
            ]);

            $response->assertStatus(302);

            // Verify the bulk action was executed
            foreach ($postIds as $postId) {
                $post = Post::find($postId);
                if ($post && $draftPosts->contains('id', $postId)) {
                    expect($post->status)->toBe('published');
                }
            }
        });

        it('respects action authorization in workflow', function () {
            $archivedPost = $this->posts->where('status', 'archived')->first();

            // Try to edit an archived post (should be unauthorized)
            $signedUrl = URL::signedRoute('inertia-tables.action', [
                'table' => base64_encode(get_class($this->tableClass)),
                'name' => 'edit',
                'action' => base64_encode(Action::class),
                'record' => $archivedPost->id,
            ]);

            // Build table to check action visibility
            $request = Request::create('/test', 'GET');
            $result = InertiaTables::table($request)
                ->using($this->tableClass)
                ->build();

            $array = $result->toArray();

            // Find the archived post in the data
            $archivedPostData = null;
            foreach ($array['data'] as $postData) {
                if ($postData['id'] === $archivedPost->id) {
                    $archivedPostData = $postData;
                    break;
                }
            }

            // The edit action should not be available for archived posts
            if ($archivedPostData && isset($archivedPostData['actions'])) {
                $editActionAvailable = false;
                foreach ($archivedPostData['actions'] as $action) {
                    if ($action['name'] === 'edit') {
                        $editActionAvailable = true;
                        break;
                    }
                }
                expect($editActionAvailable)->toBeFalse();
            }
        });

    });

    describe('Complex Filtering and Search', function () {

        it('handles multiple search terms', function () {
            $category = $this->categories->first();
            $request = Request::create('/test', 'GET', [
                'search' => $category->name,
            ]);

            $result = InertiaTables::table($request)
                ->using($this->tableClass)
                ->build();

            $array = $result->toArray();

            // Should find posts in this category
            $foundPost = false;
            foreach ($array['data'] as $post) {
                if ($post['category']['name'] === $category->name) {
                    $foundPost = true;
                    break;
                }
            }
            expect($foundPost)->toBeTrue();
        });

        it('combines search with sorting and pagination', function () {
            $request = Request::create('/test', 'GET', [
                'search' => 'test',
                'sort' => 'created_at',
                'direction' => 'desc',
                'page' => 1,
                'per_page' => 3,
            ]);

            $result = InertiaTables::table($request)
                ->using($this->tableClass)
                ->build();

            $array = $result->toArray();

            expect($array['meta']['per_page'])->toBe(3);
            expect(count($array['data']))->toBeLessThanOrEqual(3);

            // Verify sorting by created_at desc
            if (count($array['data']) >= 2) {
                $first = new \Carbon\Carbon($array['data'][0]['created_at']);
                $second = new \Carbon\Carbon($array['data'][1]['created_at']);
                expect($first->gte($second))->toBeTrue();
            }
        });

    });

    describe('Column Formatting and Display', function () {

        it('applies column formatting correctly', function () {
            $request = Request::create('/test', 'GET');

            $result = InertiaTables::table($request)
                ->using($this->tableClass)
                ->build();

            $array = $result->toArray();

            if (! empty($array['data'])) {
                $firstPost = $array['data'][0];

                // Test date formatting
                expect($firstPost['created_at'])->toMatch('/^[A-Z][a-z]{2} \d{1,2}, \d{4}$/');

                // Test status badge
                expect(in_array($firstPost['status'], ['published', 'draft', 'archived']))->toBeTrue();

                // Test relationship data
                expect($firstPost['user'])->toHaveKey('name');
                expect($firstPost['category'])->toHaveKey('name');
            }
        });

        it('includes proper column metadata', function () {
            $request = Request::create('/test', 'GET');

            $result = InertiaTables::table($request)
                ->using($this->tableClass)
                ->build();

            $array = $result->toArray();

            expect($array['columns'])->toBeArray();

            $titleColumn = null;
            foreach ($array['columns'] as $column) {
                if ($column['key'] === 'title') {
                    $titleColumn = $column;
                    break;
                }
            }

            expect($titleColumn)->not->toBeNull();
            expect($titleColumn['sortable'])->toBeTrue();
            expect($titleColumn['searchable'])->toBeTrue();
        });

    });

    describe('Performance and Edge Cases', function () {

        it('handles large datasets efficiently', function () {
            // Create more test data
            $largeUserSet = User::factory()->count(50)->create();
            $largePosts = Post::factory()->count(100)->create([
                'user_id' => fn () => $largeUserSet->random()->id,
                'category_id' => fn () => $this->categories->random()->id,
            ]);

            $request = Request::create('/test', 'GET', [
                'per_page' => 20,
            ]);

            $start = microtime(true);

            $result = InertiaTables::table($request)
                ->using($this->tableClass)
                ->build();

            $end = microtime(true);
            $executionTime = $end - $start;

            $array = $result->toArray();

            expect($array['data'])->toBeArray();
            expect(count($array['data']))->toBeLessThanOrEqual(20);
            expect($array['meta']['total'])->toBeGreaterThan(100);

            // Performance should be reasonable (less than 1 second for this test)
            expect($executionTime)->toBeLessThan(1.0);
        });

        it('handles empty search results gracefully', function () {
            $request = Request::create('/test', 'GET', [
                'search' => 'nonexistent_search_term_xyz123',
            ]);

            $result = InertiaTables::table($request)
                ->using($this->tableClass)
                ->build();

            $array = $result->toArray();

            expect($array['data'])->toBeArray();
            expect(count($array['data']))->toBe(0);
            expect($array['meta']['total'])->toBe(0);
            expect($array['columns'])->toBeArray(); // Columns should still be present
            expect($array['actions'])->toBeArray(); // Actions should still be present
        });

        it('handles invalid sort column gracefully', function () {
            $request = Request::create('/test', 'GET', [
                'sort' => 'nonexistent_column',
                'direction' => 'asc',
            ]);

            $result = InertiaTables::table($request)
                ->using($this->tableClass)
                ->build();

            $array = $result->toArray();

            // Should fallback to default sorting or handle gracefully
            expect($array['data'])->toBeArray();
            expect($array['meta']['total'])->toBeGreaterThan(0);
        });

        it('handles pagination edge cases', function () {
            $request = Request::create('/test', 'GET', [
                'page' => 999, // Page beyond available data
                'per_page' => 10,
            ]);

            $result = InertiaTables::table($request)
                ->using($this->tableClass)
                ->build();

            $array = $result->toArray();

            expect($array['data'])->toBeArray();
            expect($array['meta'])->toHaveKey('current_page');
            expect($array['meta'])->toHaveKey('total');
        });

    });

    describe('Real-world Usage Scenarios', function () {

        it('supports admin dashboard table functionality', function () {
            // Simulate admin dashboard with search, sort, filter
            $request = Request::create('/admin/posts', 'GET', [
                'search' => 'draft',
                'sort' => 'created_at',
                'direction' => 'desc',
                'per_page' => 15,
            ]);

            $result = InertiaTables::table($request)
                ->using($this->tableClass)
                ->build();

            $array = $result->toArray();

            expect($array)->toHaveKeys(['data', 'meta', 'columns', 'actions', 'bulkActions', 'config']);
            expect($array['meta']['per_page'])->toBe(15);
        });

        it('supports content management workflow', function () {
            // Create posts in different states
            $draftPost = Post::factory()->create(['status' => 'draft']);
            $publishedPost = Post::factory()->create(['status' => 'published']);

            $request = Request::create('/content/posts', 'GET');

            $result = InertiaTables::table($request)
                ->using($this->tableClass)
                ->build();

            $array = $result->toArray();

            // Should have different actions available based on post status
            expect($array['data'])->toBeArray();
            expect($array['actions'])->toBeArray();
            expect($array['bulkActions'])->toBeArray();

            // Verify bulk actions are available
            $bulkPublish = false;
            foreach ($array['bulkActions'] as $action) {
                if ($action['name'] === 'bulk_publish') {
                    $bulkPublish = true;
                    break;
                }
            }
            expect($bulkPublish)->toBeTrue();
        });

        it('handles concurrent user interactions', function () {
            // Simulate multiple users requesting table data
            $requests = [
                Request::create('/test', 'GET', ['search' => 'user1']),
                Request::create('/test', 'GET', ['sort' => 'title', 'direction' => 'asc']),
                Request::create('/test', 'GET', ['page' => 2, 'per_page' => 5]),
            ];

            $results = [];
            foreach ($requests as $request) {
                $result = InertiaTables::table($request)
                    ->using($this->tableClass)
                    ->build();

                $results[] = $result->toArray();
            }

            // All requests should succeed
            expect(count($results))->toBe(3);
            foreach ($results as $result) {
                expect($result)->toHaveKeys(['data', 'meta', 'columns']);
            }
        });

    });

});
