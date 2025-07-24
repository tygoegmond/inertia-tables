<?php

use Egmond\InertiaTables\Actions\Action;
use Egmond\InertiaTables\Actions\BulkAction;
use Egmond\InertiaTables\Builder\TableBuilder;
use Egmond\InertiaTables\Columns\TextColumn;
use Egmond\InertiaTables\TableResult;
use Egmond\InertiaTables\Tests\Database\Models\Category;
use Egmond\InertiaTables\Tests\Database\Models\Comment;
use Egmond\InertiaTables\Tests\Database\Models\Post;
use Egmond\InertiaTables\Tests\Database\Models\User;
use Illuminate\Http\Request;

describe('TableBuilder Class', function () {

    beforeEach(function () {
        $this->request = new Request;
        $this->builder = new TableBuilder($this->request);
    });

    describe('Instantiation', function () {

        it('can be instantiated with request', function () {
            expect($this->builder)->toBeInstanceOf(TableBuilder::class);
        });

        it('can be instantiated without request', function () {
            $builder = new TableBuilder;
            expect($builder)->toBeInstanceOf(TableBuilder::class);
        });

        it('can be created using make method', function () {
            $builder = TableBuilder::make($this->request);
            expect($builder)->toBeInstanceOf(TableBuilder::class);
        });

        it('can be created using make method without request', function () {
            $builder = TableBuilder::make();
            expect($builder)->toBeInstanceOf(TableBuilder::class);
        });

    });

    describe('Column Management', function () {

        it('can add columns fluently', function () {
            $columns = [
                TextColumn::make('name'),
                TextColumn::make('email'),
            ];

            $result = $this->builder->columns($columns);

            expect($result)->toBe($this->builder);
        });

        it('can add individual columns', function () {
            $column = TextColumn::make('name');

            $result = $this->builder->addColumn($column);

            expect($result)->toBe($this->builder);
        });

    });

    describe('Configuration Methods', function () {

        it('can set pagination fluently', function () {
            $result = $this->builder->paginate(50);

            expect($result)->toBe($this->builder);
        });

        it('can set sorting fluently', function () {
            $result = $this->builder->sortBy('name', 'desc');

            expect($result)->toBe($this->builder);
        });

        it('can set searchable fluently', function () {
            $result = $this->builder->searchable();

            expect($result)->toBe($this->builder);
        });

        it('can set name fluently', function () {
            $result = $this->builder->setName('users');

            expect($result)->toBe($this->builder);
        });

        it('can set table class fluently', function () {
            $result = $this->builder->setTableClass('App\\Tables\\UserTable');

            expect($result)->toBe($this->builder);
        });

        it('can set actions fluently', function () {
            $actions = [Action::make('edit')];
            $result = $this->builder->actions($actions);

            expect($result)->toBe($this->builder);
        });

        it('can set bulk actions fluently', function () {
            $bulkActions = [BulkAction::make('delete')];
            $result = $this->builder->bulkActions($bulkActions);

            expect($result)->toBe($this->builder);
        });

        it('can set header actions fluently', function () {
            $headerActions = [Action::make('create')];
            $result = $this->builder->headerActions($headerActions);

            expect($result)->toBe($this->builder);
        });

    });

    describe('Query Building and Data Processing', function () {

        beforeEach(function () {
            // Create test data
            $this->users = User::factory()->count(10)->create();
            $this->builder
                ->columns([
                    TextColumn::make('name')->searchable(),
                    TextColumn::make('email')->searchable(),
                    TextColumn::make('status'),
                ])
                ->setName('users');
        });

        it('builds table result with basic query', function () {
            $query = User::query();
            $result = $this->builder->build($query);

            expect($result)->toBeInstanceOf(TableResult::class);
            expect($result->name)->toBe('users');
            expect($result->data)->toBeArray();
            expect(count($result->data))->toBe(10);
        });

        it('applies pagination correctly', function () {
            $query = User::query();
            $result = $this->builder
                ->paginate(5)
                ->build($query);

            expect(count($result->data))->toBe(5);
            expect($result->pagination['per_page'])->toBe(5);
            expect($result->pagination['total'])->toBe(10);
        });

        it('includes pagination metadata', function () {
            $query = User::query();
            $result = $this->builder->build($query);

            expect($result->pagination)->toHaveKeys([
                'current_page',
                'per_page',
                'total',
                'last_page',
                'from',
                'to',
                'links',
            ]);
        });

        it('applies default sorting', function () {
            // Clear existing users and create specific test data
            User::query()->delete();
            $userC = User::factory()->create(['name' => 'Charlie']);
            $userA = User::factory()->create(['name' => 'Alice']);
            $userB = User::factory()->create(['name' => 'Bob']);

            $builder = new TableBuilder;
            $builder
                ->columns([TextColumn::make('name')->sortable()])
                ->sortBy('name', 'desc')
                ->setName('users');

            $query = User::query();
            $result = $builder->build($query);

            $names = array_column($result->data, 'name');

            // Should be sorted in descending order: Charlie, Bob, Alice
            expect(count($names))->toBe(3);
            expect($names[0])->toBe('Charlie');
            expect($names[2])->toBe('Alice');
        });

    });

    describe('Search Functionality', function () {

        beforeEach(function () {
            User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
            User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);
            User::factory()->create(['name' => 'Bob Wilson', 'email' => 'bob@example.com']);

            $this->builder
                ->columns([
                    TextColumn::make('name')->searchable(),
                    TextColumn::make('email')->searchable(),
                    TextColumn::make('status'),
                ])
                ->searchable()
                ->setName('users');
        });

        it('filters results based on search query in request', function () {
            $request = new Request(['users' => ['search' => 'John']]);
            $builder = new TableBuilder($request);
            $builder
                ->columns([
                    TextColumn::make('name')->searchable(),
                    TextColumn::make('email')->searchable(),
                ])
                ->searchable()
                ->setName('users');

            $query = User::query();
            $result = $builder->build($query);

            expect(count($result->data))->toBe(1);
            expect($result->data[0]['name'])->toBe('John Doe');
        });

        it('searches across multiple searchable columns', function () {
            $request = new Request(['users' => ['search' => 'example.com']]);
            $builder = new TableBuilder($request);
            $builder
                ->columns([
                    TextColumn::make('name')->searchable(),
                    TextColumn::make('email')->searchable(),
                ])
                ->searchable()
                ->setName('users');

            $query = User::query();
            $result = $builder->build($query);

            expect(count($result->data))->toBe(3);
        });

        it('returns all results when no search query', function () {
            $query = User::query();
            $result = $this->builder->build($query);

            expect(count($result->data))->toBe(3);
        });

        it('ignores search when table is not searchable', function () {
            $request = new Request(['users' => ['search' => 'John']]);
            $builder = new TableBuilder($request);
            $builder
                ->columns([
                    TextColumn::make('name')->searchable(),
                    TextColumn::make('email')->searchable(),
                ])
                ->searchable(false)
                ->setName('users');

            $query = User::query();
            $result = $builder->build($query);

            expect(count($result->data))->toBe(3);
        });

    });

    describe('Sorting Functionality', function () {

        beforeEach(function () {
            User::factory()->create(['name' => 'Alice']);
            User::factory()->create(['name' => 'Bob']);
            User::factory()->create(['name' => 'Charlie']);

            $this->builder
                ->columns([
                    TextColumn::make('name')->sortable(),
                    TextColumn::make('email'),
                ])
                ->setName('users');
        });

        it('applies sorting from request parameters', function () {
            $request = new Request(['users' => ['sort' => 'name', 'direction' => 'desc']]);
            $builder = new TableBuilder($request);
            $builder
                ->columns([TextColumn::make('name')->sortable()])
                ->setName('users');

            $query = User::query();
            $result = $builder->build($query);

            $names = array_column($result->data, 'name');
            expect($names[0])->toBe('Charlie');
            expect($names[2])->toBe('Alice');
        });

        it('defaults to asc direction when not specified', function () {
            $request = new Request(['users' => ['sort' => 'name']]);
            $builder = new TableBuilder($request);
            $builder
                ->columns([TextColumn::make('name')->sortable()])
                ->setName('users');

            $query = User::query();
            $result = $builder->build($query);

            $names = array_column($result->data, 'name');
            expect($names[0])->toBe('Alice');
            expect($names[2])->toBe('Charlie');
        });

        it('ignores sorting for non-sortable columns', function () {
            $request = new Request(['users' => ['sort' => 'email']]);
            $builder = new TableBuilder($request);
            $builder
                ->columns([
                    TextColumn::make('name'),
                    TextColumn::make('email'), // not sortable
                ])
                ->setName('users');

            $query = User::query();
            $result = $builder->build($query);

            // Should maintain database order since email column is not sortable
            expect(count($result->data))->toBe(3);
        });

    });

    describe('Relationship Handling', function () {

        beforeEach(function () {
            $this->category = Category::factory()->create();
            $this->user = User::factory()->create();
            $this->posts = Post::factory()->count(3)->create([
                'user_id' => $this->user->id,
                'category_id' => $this->category->id,
            ]);
            Comment::factory()->count(2)->create(['post_id' => $this->posts[0]->id]);
            Comment::factory()->count(1)->create(['post_id' => $this->posts[1]->id]);
        });

        it('handles relationship columns with dot notation', function () {
            $builder = new TableBuilder;
            $builder
                ->columns([
                    TextColumn::make('title'),
                    TextColumn::make('user.name'),
                    TextColumn::make('category.name'),
                ])
                ->setName('posts');

            $query = Post::query();
            $result = $builder->build($query);

            expect(count($result->data))->toBe(3);
            expect($result->data[0])->toHaveKey('user.name');
            expect($result->data[0])->toHaveKey('category.name');
        });

        it('applies relationship counts', function () {
            $builder = new TableBuilder;
            $builder
                ->columns([
                    TextColumn::make('title'),
                    TextColumn::make('comments_count')->counts('comments'),
                ])
                ->setName('posts');

            $query = Post::query();
            $result = $builder->build($query);

            expect($result->data[0]['comments_count'])->toBe('2');
            expect($result->data[1]['comments_count'])->toBe('1');
            expect($result->data[2]['comments_count'])->toBe('0');
        });

    });

    describe('Data Transformation', function () {

        beforeEach(function () {
            $this->user = User::factory()->create();
        });

        it('includes primary key in transformed data', function () {
            $query = User::query();
            $result = $this->builder
                ->columns([TextColumn::make('name')])
                ->setName('users')
                ->build($query);

            expect($result->data[0])->toHaveKey('id');
            expect($result->data[0]['id'])->toBe($this->user->id);
            expect($result->primaryKey)->toBe('id');
        });

        it('transforms data according to column formatters', function () {
            $query = User::query();
            $result = $this->builder
                ->columns([
                    TextColumn::make('name'),
                    TextColumn::make('email'),
                ])
                ->setName('users')
                ->build($query);

            expect($result->data[0]['name'])->toBe($this->user->name);
            expect($result->data[0]['email'])->toBe($this->user->email);
        });

    });

    describe('Configuration Data', function () {

        it('returns correct configuration', function () {
            $columns = [
                TextColumn::make('name')->searchable(),
                TextColumn::make('email'),
            ];

            $query = User::factory()->create();
            $result = $this->builder
                ->columns($columns)
                ->searchable()
                ->paginate(50)
                ->sortBy('name', 'desc')
                ->setName('users')
                ->build(User::query());

            expect($result->config)->toHaveKeys([
                'columns',
                'searchable',
                'perPage',
                'defaultSort',
            ]);

            expect($result->config['searchable'])->toBeTrue();
            expect($result->config['perPage'])->toBe(50);
            expect($result->config['defaultSort'])->toBe(['name' => 'desc']);
            expect(count($result->config['columns']))->toBe(2);
        });

    });

    describe('Action Serialization', function () {

        beforeEach(function () {
            $this->user = User::factory()->create();
        });

        it('serializes actions correctly', function () {
            $actions = [Action::make('edit')];

            $result = $this->builder
                ->columns([TextColumn::make('name')])
                ->actions($actions)
                ->setName('users')
                ->setTableClass('App\\Tables\\UserTable')
                ->build(User::query());

            expect($result->actions)->toBeArray();
            expect(count($result->actions))->toBe(1);
        });

        it('serializes bulk actions correctly', function () {
            $bulkActions = [BulkAction::make('delete')];

            $result = $this->builder
                ->columns([TextColumn::make('name')])
                ->bulkActions($bulkActions)
                ->setName('users')
                ->setTableClass('App\\Tables\\UserTable')
                ->build(User::query());

            expect($result->bulkActions)->toBeArray();
            expect(count($result->bulkActions))->toBe(1);
        });

        it('serializes header actions correctly', function () {
            $headerActions = [Action::make('create')];

            $result = $this->builder
                ->columns([TextColumn::make('name')])
                ->headerActions($headerActions)
                ->setName('users')
                ->build(User::query());

            expect($result->headerActions)->toBeArray();
            expect(count($result->headerActions))->toBe(1);
        });

    });

    describe('Request Parameter Handling', function () {

        it('extracts page number from request', function () {
            $request = new Request(['users' => ['page' => '3']]);
            $builder = new TableBuilder($request);

            // Create enough data to have multiple pages
            User::factory()->count(30)->create();

            $result = $builder
                ->columns([TextColumn::make('name')])
                ->paginate(10)
                ->setName('users')
                ->build(User::query());

            expect($result->pagination['current_page'])->toBe(3);
        });

        it('handles missing request parameters gracefully', function () {
            $request = new Request;
            $builder = new TableBuilder($request);

            User::factory()->create();

            $result = $builder
                ->columns([TextColumn::make('name')])
                ->setName('users')
                ->build(User::query());

            expect($result->pagination['current_page'])->toBe(1);
            expect($result->search)->toBeNull();
            expect($result->sort)->toBe([]);
        });

    });

});
