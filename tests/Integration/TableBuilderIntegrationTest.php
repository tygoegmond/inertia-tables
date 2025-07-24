<?php

use Egmond\InertiaTables\Actions\Action;
use Egmond\InertiaTables\Actions\BulkAction;
use Egmond\InertiaTables\Builder\TableBuilder;
use Egmond\InertiaTables\Columns\TextColumn;
use Egmond\InertiaTables\Table;
use Egmond\InertiaTables\TableResult;
use Egmond\InertiaTables\Tests\Database\Models\Category;
use Egmond\InertiaTables\Tests\Database\Models\Comment;
use Egmond\InertiaTables\Tests\Database\Models\Post;
use Egmond\InertiaTables\Tests\Database\Models\User;
use Illuminate\Http\Request;

describe('Table Integration Tests', function () {

    describe('Full Table Building Workflow', function () {

        beforeEach(function () {
            // Create test data with relationships
            $this->category = Category::factory()->create(['name' => 'Technology']);
            $this->users = User::factory()->count(5)->create();

            $this->posts = collect();
            foreach ($this->users as $user) {
                $posts = Post::factory()->count(2)->create([
                    'user_id' => $user->id,
                    'category_id' => $this->category->id,
                ]);
                $this->posts = $this->posts->merge($posts);
            }

            // Add comments to some posts
            Comment::factory()->count(3)->create(['post_id' => $this->posts->first()->id]);
            Comment::factory()->count(1)->create(['post_id' => $this->posts->get(1)->id]);
        });

        it('can build a complete table with all features', function () {
            $table = (new Table)
                ->query(User::query())
                ->as('users')
                ->columns([
                    TextColumn::make('name')->searchable()->sortable(),
                    TextColumn::make('email')->searchable(),
                    TextColumn::make('status')->badge(),
                    TextColumn::make('posts_count')->counts('posts'),
                ])
                ->searchable()
                ->paginate(10)
                ->defaultSort('name', 'asc')
                ->actions([
                    Action::make('edit')->label('Edit User'),
                    Action::make('delete')->color('danger'),
                ])
                ->bulkActions([
                    BulkAction::make('delete')->label('Delete Selected'),
                ])
                ->headerActions([
                    Action::make('create')->label('Add User'),
                ])
                ->setTableClass('App\\Tables\\UserTable');

            $result = $table->build();

            expect($result)->toBeInstanceOf(TableResult::class);
            expect($result->name)->toBe('users');
            expect(count($result->data))->toBeGreaterThanOrEqual(5);
            expect($result->config['searchable'])->toBeTrue();
            expect($result->config['perPage'])->toBe(10);
            expect($result->config['defaultSort'])->toBe(['name' => 'asc']);
            expect(count($result->config['columns']))->toBe(4);
            expect(count($result->actions))->toBe(2);
            expect(count($result->bulkActions))->toBe(1);
            expect(count($result->headerActions))->toBe(1);
        });

        it('handles complex relationships and aggregations', function () {
            $table = (new Table)
                ->query(Post::query())
                ->as('posts')
                ->columns([
                    TextColumn::make('title')->searchable(),
                    TextColumn::make('user.name')->label('Author'),
                    TextColumn::make('category.name')->label('Category'),
                    TextColumn::make('comments_count')->counts('comments'),
                    TextColumn::make('views')->sortable(),
                ])
                ->searchable()
                ->paginate(25);

            $result = $table->build();

            expect($result)->toBeInstanceOf(TableResult::class);
            expect(count($result->data))->toBe(10); // 5 users × 2 posts each

            // Check that relationship data is loaded
            $firstPost = $result->data[0];
            expect($firstPost)->toHaveKey('user.name');
            expect($firstPost)->toHaveKey('category.name');
            expect($firstPost)->toHaveKey('comments_count');
            expect($firstPost['category.name'])->toBe('Technology');
        });

        it('applies search across multiple columns', function () {
            $request = new Request(['users' => ['search' => $this->users->first()->name]]);

            $builder = TableBuilder::make($request)
                ->columns([
                    TextColumn::make('name')->searchable(),
                    TextColumn::make('email')->searchable(),
                ])
                ->searchable()
                ->setName('users');

            $result = $builder->build(User::query());

            expect(count($result->data))->toBe(1);
            expect($result->data[0]['name'])->toBe($this->users->first()->name);
            expect($result->search)->toBe($this->users->first()->name);
        });

        it('handles sorting with request parameters', function () {
            $request = new Request(['users' => ['sort' => 'name', 'direction' => 'desc']]);

            $builder = TableBuilder::make($request)
                ->columns([
                    TextColumn::make('name')->sortable(),
                    TextColumn::make('email'),
                ])
                ->setName('users');

            $result = $builder->build(User::query());

            $names = array_column($result->data, 'name');
            $sortedNames = $names;
            rsort($sortedNames);

            expect($names)->toBe($sortedNames);
            expect($result->sort)->toBe(['name' => 'desc']);
        });

        it('handles pagination with request parameters', function () {
            // Create more users for pagination testing
            User::factory()->count(15)->create();

            $request = new Request(['users' => ['page' => '2']]);

            $builder = TableBuilder::make($request)
                ->columns([TextColumn::make('name')])
                ->paginate(10)
                ->setName('users');

            $result = $builder->build(User::query());

            expect($result->pagination['current_page'])->toBe(2);
            expect($result->pagination['per_page'])->toBe(10);
            expect($result->pagination['total'])->toBeGreaterThan(15); // At least 5 original + 15 new
            expect(count($result->data))->toBe(10);
        });

    });

    describe('Action Integration with Records', function () {

        beforeEach(function () {
            $this->users = User::factory()->count(3)->create([
                'status' => 'active',
            ]);
            $this->inactiveUser = User::factory()->create([
                'status' => 'inactive',
            ]);
        });

        it('generates proper action data for each row', function () {
            $editAction = Action::make('edit')
                ->authorize(fn ($record) => $record->status === 'active')
                ->disabled(fn ($record) => $record->email === 'admin@example.com');

            $deleteAction = Action::make('delete')
                ->color('danger')
                ->hidden(fn ($record) => $record->id === 1);

            $table = (new Table)
                ->query(User::query())
                ->as('users')
                ->columns([TextColumn::make('name')])
                ->actions([$editAction, $deleteAction])
                ->setTableClass('App\\Tables\\UserTable');

            $result = $table->build();

            expect(count($result->data))->toBe(4);

            // Check that each row has action data
            foreach ($result->data as $row) {
                expect($row)->toHaveKey('actions');
                expect($row['actions'])->toBeArray();
            }
        });

        it('respects action authorization and visibility', function () {
            $action = Action::make('edit')
                ->authorize(fn ($record) => $record->status === 'active')
                ->visible(fn ($record) => $record->status !== 'banned');

            $builder = TableBuilder::make()
                ->columns([TextColumn::make('name'), TextColumn::make('status')])
                ->actions([$action])
                ->setName('users')
                ->setTableClass('App\\Tables\\UserTable');

            $result = $builder->build(User::query());

            // Active users should have the action
            $activeUserRow = collect($result->data)->first(fn ($row) => $row['status'] === 'active');
            expect($activeUserRow['actions'])->toHaveKey('edit');

            // Inactive users should not have the action (not authorized)
            $inactiveUserRow = collect($result->data)->first(fn ($row) => $row['status'] === 'inactive');
            expect($inactiveUserRow['actions'])->not->toHaveKey('edit');
        });

    });

    describe('Column Formatting Integration', function () {

        beforeEach(function () {
            $this->user = User::factory()->create([
                'name' => 'John Doe',
                'salary' => 50000.00,
                'status' => 'active',
            ]);
        });

        it('applies column formatting correctly', function () {
            $table = (new Table)
                ->query(User::query())
                ->as('users')
                ->columns([
                    TextColumn::make('name')->prefix('Mr. ')->suffix(' (User)'),
                    TextColumn::make('salary')->prefix('$')->limit(10),
                    TextColumn::make('status')->badge(),
                ])
                ->paginate(10);

            $result = $table->build();

            $userRow = $result->data[0];
            expect($userRow['name'])->toBe('Mr. John Doe (User)');
            expect($userRow['salary'])->toBe('$50000.00');
            expect($userRow['status'])->toBe('active');

            // Check badge metadata
            expect($userRow)->toHaveKey('meta');
            expect($userRow['meta']['badgeVariant'])->toHaveKey('status');
        });

        it('handles relationship columns with formatting', function () {
            $category = Category::factory()->create(['name' => 'Technology News']);
            $post = Post::factory()->create([
                'title' => 'Laravel Best Practices for Modern Development',
                'user_id' => $this->user->id,
                'category_id' => $category->id,
            ]);

            $table = (new Table)
                ->query(Post::query())
                ->as('posts')
                ->columns([
                    TextColumn::make('title')->limit(20),
                    TextColumn::make('user.name')->label('Author')->prefix('By: '),
                    TextColumn::make('category.name')->label('Category')->badge(),
                ]);

            $result = $table->build();

            $postRow = $result->data[0];
            expect($postRow['title'])->toBe('Laravel Best Practic...');
            expect($postRow['user.name'])->toBe('By: John Doe');
            expect($postRow['category.name'])->toBe('Technology News');
        });

    });

    describe('Complex Query Scenarios', function () {

        beforeEach(function () {
            // Create users with posts and comments
            $this->author = User::factory()->create(['name' => 'Author User']);
            $this->regularUser = User::factory()->create(['name' => 'Regular User']);

            $this->post1 = Post::factory()->create([
                'title' => 'Popular Post',
                'user_id' => $this->author->id,
                'views' => 1000,
            ]);

            $this->post2 = Post::factory()->create([
                'title' => 'Less Popular Post',
                'user_id' => $this->author->id,
                'views' => 100,
            ]);

            Comment::factory()->count(5)->create(['post_id' => $this->post1->id]);
            Comment::factory()->count(2)->create(['post_id' => $this->post2->id]);
        });

        it('handles multiple aggregations correctly', function () {
            $table = (new Table)
                ->query(User::query())
                ->as('users')
                ->columns([
                    TextColumn::make('name'),
                    TextColumn::make('posts_count')->counts('posts'),
                ]);

            $result = $table->build();

            $authorRow = collect($result->data)->first(fn ($row) => $row['name'] === 'Author User');
            expect($authorRow['posts_count'])->toBe('2');

            $regularUserRow = collect($result->data)->first(fn ($row) => $row['name'] === 'Regular User');
            expect($regularUserRow['posts_count'])->toBe('0');
        });

        it('combines search, sort, and pagination with relationships', function () {
            $request = new Request([
                'posts' => [
                    'search' => 'Popular',
                    'sort' => 'views',
                    'direction' => 'desc',
                    'page' => '1',
                ],
            ]);

            $builder = TableBuilder::make($request)
                ->columns([
                    TextColumn::make('title')->searchable(),
                    TextColumn::make('user.name')->label('Author'),
                    TextColumn::make('views')->sortable(),
                    TextColumn::make('comments_count')->counts('comments'),
                ])
                ->searchable()
                ->paginate(5)
                ->setName('posts');

            $result = $builder->build(Post::query());

            expect(count($result->data))->toBeGreaterThanOrEqual(1); // At least "Popular Post" matches search, maybe more partial matches
            expect($result->data[0]['title'])->toBe('Popular Post');
            expect($result->data[0]['user.name'])->toBe('Author User');
            expect($result->data[0]['comments_count'])->toBe('5');
            expect($result->search)->toBe('Popular');
            expect($result->sort)->toBe(['views' => 'desc']);
        });

    });

    describe('Error Handling and Edge Cases', function () {

        it('handles empty result sets gracefully', function () {
            $table = (new Table)
                ->query(User::where('id', -1)) // No results
                ->as('empty_users')
                ->columns([
                    TextColumn::make('name'),
                    TextColumn::make('email'),
                ]);

            $result = $table->build();

            expect($result->data)->toBe([]);
            expect($result->pagination['total'])->toBe(0);
            expect($result->pagination['current_page'])->toBe(1);
        });

        it('handles missing relationships gracefully', function () {
            // Create a post without explicit user relationship setup
            $orphanPost = Post::factory()->create([
                'title' => 'Orphan Post',
                'user_id' => 999, // Non-existent user
            ]);

            $table = (new Table)
                ->query(Post::where('id', $orphanPost->id))
                ->as('posts')
                ->columns([
                    TextColumn::make('title'),
                    TextColumn::make('user.name')->label('Author'),
                ]);

            $result = $table->build();

            expect(count($result->data))->toBe(1);
            expect($result->data[0]['title'])->toBe('Orphan Post');
            expect($result->data[0]['user.name'])->toBeNull();
        });

        it('handles large datasets efficiently', function () {
            // Create many users
            User::factory()->count(100)->create();

            $table = (new Table)
                ->query(User::query())
                ->as('users')
                ->columns([
                    TextColumn::make('name')->searchable(),
                    TextColumn::make('email'),
                ])
                ->searchable()
                ->paginate(20);

            $result = $table->build();

            expect($result->pagination['total'])->toBeGreaterThanOrEqual(100);
            expect(count($result->data))->toBe(20);
            expect($result->pagination['last_page'])->toBeGreaterThanOrEqual(5);
        });

    });

});
