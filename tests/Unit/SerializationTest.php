<?php

use Egmond\InertiaTables\Actions\Action;
use Egmond\InertiaTables\Columns\TextColumn;
use Egmond\InertiaTables\Serialization\Serializer;
use Egmond\InertiaTables\TableResult;
use Egmond\InertiaTables\Tests\Database\Models\User;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

describe('Serializer Class', function () {

    describe('TableResult Serialization', function () {

        beforeEach(function () {
            $this->config = [
                'columns' => [
                    ['key' => 'name', 'label' => 'Name', 'type' => 'text'],
                    ['key' => 'email', 'label' => 'Email', 'type' => 'text'],
                ],
                'searchable' => true,
                'perPage' => 25,
                'defaultSort' => ['name' => 'asc'],
            ];

            $this->data = [
                ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
                ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com'],
            ];

            $this->pagination = [
                'current_page' => 1,
                'per_page' => 25,
                'total' => 2,
                'last_page' => 1,
                'from' => 1,
                'to' => 2,
                'links' => [
                    ['url' => null, 'label' => '&laquo; Previous', 'active' => false],
                    ['url' => 'http://example.com?page=1', 'label' => '1', 'active' => true],
                    ['url' => null, 'label' => 'Next &raquo;', 'active' => false],
                ],
            ];

            $this->tableResult = new TableResult(
                config: $this->config,
                data: $this->data,
                pagination: $this->pagination,
                sort: ['name' => 'asc'],
                search: 'john',
                name: 'users',
                actions: [Action::make('edit')],
                bulkActions: [Action::make('delete')],
                headerActions: [Action::make('create')],
                primaryKey: 'id',
            );
        });

        it('serializes TableResult correctly', function () {
            $serialized = Serializer::serializeTableResult($this->tableResult);

            expect($serialized)->toBeArray();
            expect($serialized)->toHaveKeys([
                'config', 'data', 'pagination', 'sort', 'search',
                'name', 'actions', 'bulkActions', 'headerActions', 'primaryKey',
            ]);

            expect($serialized['name'])->toBe('users');
            expect($serialized['search'])->toBe('john');
            expect($serialized['sort'])->toBe(['name' => 'asc']);
            expect($serialized['primaryKey'])->toBe('id');
        });

        it('serializes config with defaults', function () {
            $serialized = Serializer::serializeTableResult($this->tableResult);
            $config = $serialized['config'];

            expect($config)->toHaveKeys(['columns', 'searchable', 'perPage', 'defaultSort']);
            expect($config['searchable'])->toBeTrue();
            expect($config['perPage'])->toBe(25);
            expect($config['defaultSort'])->toBe(['name' => 'asc']);
            expect($config['columns'])->toBe($this->config['columns']);
        });

        it('serializes pagination with defaults', function () {
            $serialized = Serializer::serializeTableResult($this->tableResult);
            $pagination = $serialized['pagination'];

            expect($pagination)->toHaveKeys([
                'current_page', 'per_page', 'total', 'last_page',
                'from', 'to', 'links',
            ]);

            expect($pagination['current_page'])->toBe(1);
            expect($pagination['per_page'])->toBe(25);
            expect($pagination['total'])->toBe(2);
            expect($pagination['links'])->toBeArray();
        });

        it('serializes actions correctly', function () {
            $serialized = Serializer::serializeTableResult($this->tableResult);

            expect($serialized['actions'])->toBeArray();
            expect($serialized['bulkActions'])->toBeArray();
            expect($serialized['headerActions'])->toBeArray();
        });

    });

    describe('Config Serialization', function () {

        it('serializes config with all properties', function () {
            $config = [
                'columns' => [
                    TextColumn::make('name')->searchable(),
                    TextColumn::make('email'),
                ],
                'searchable' => true,
                'perPage' => 50,
                'defaultSort' => ['name' => 'desc'],
            ];

            $serialized = Serializer::serializeConfig($config);

            expect($serialized['columns'])->toBeArray();
            expect(count($serialized['columns']))->toBe(2);
            expect($serialized['searchable'])->toBeTrue();
            expect($serialized['perPage'])->toBe(50);
            expect($serialized['defaultSort'])->toBe(['name' => 'desc']);
        });

        it('applies defaults for missing config values', function () {
            $config = []; // Empty config

            $serialized = Serializer::serializeConfig($config);

            expect($serialized['columns'])->toBe([]);
            expect($serialized['searchable'])->toBeFalse();
            expect($serialized['perPage'])->toBe(25);
            expect($serialized['defaultSort'])->toBe([]);
        });

        it('serializes BaseColumn objects in columns', function () {
            $column = TextColumn::make('name')->searchable()->sortable();
            $config = ['columns' => [$column]];

            $serialized = Serializer::serializeConfig($config);

            expect($serialized['columns'][0])->toBeArray();
            expect($serialized['columns'][0]['key'])->toBe('name');
            expect($serialized['columns'][0]['searchable'])->toBeTrue();
            expect($serialized['columns'][0]['sortable'])->toBeTrue();
        });

    });

    describe('Column Serialization', function () {

        it('serializes BaseColumn objects', function () {
            $columns = [
                TextColumn::make('name')->label('Full Name')->searchable(),
                TextColumn::make('email')->sortable(),
            ];

            $serialized = Serializer::serializeColumns($columns);

            expect($serialized)->toBeArray();
            expect(count($serialized))->toBe(2);

            expect($serialized[0]['key'])->toBe('name');
            expect($serialized[0]['label'])->toBe('Full Name');
            expect($serialized[0]['searchable'])->toBeTrue();

            expect($serialized[1]['key'])->toBe('email');
            expect($serialized[1]['sortable'])->toBeTrue();
        });

        it('passes through non-BaseColumn items unchanged', function () {
            $columns = [
                TextColumn::make('name'),
                ['key' => 'custom', 'label' => 'Custom Column'], // Plain array
            ];

            $serialized = Serializer::serializeColumns($columns);

            expect($serialized[0])->toBeArray();
            expect($serialized[0]['key'])->toBe('name');

            expect($serialized[1])->toBe(['key' => 'custom', 'label' => 'Custom Column']);
        });

    });

    describe('Data Serialization', function () {

        it('serializes plain arrays unchanged', function () {
            $data = [
                ['id' => 1, 'name' => 'John'],
                ['id' => 2, 'name' => 'Jane'],
            ];

            $serialized = Serializer::serializeData($data);

            expect($serialized)->toBe($data);
        });

        it('serializes Arrayable objects', function () {
            $arrayableObject = new class implements Arrayable
            {
                public function toArray(): array
                {
                    return ['id' => 1, 'name' => 'Test'];
                }
            };

            $data = [$arrayableObject];
            $serialized = Serializer::serializeData($data);

            expect($serialized[0])->toBe(['id' => 1, 'name' => 'Test']);
        });

        it('serializes Jsonable objects', function () {
            $jsonableObject = new class implements Jsonable
            {
                public function toJson($options = 0): string
                {
                    return json_encode(['id' => 2, 'name' => 'JSON Test']);
                }
            };

            $data = [$jsonableObject];
            $serialized = Serializer::serializeData($data);

            expect($serialized[0])->toBe(['id' => 2, 'name' => 'JSON Test']);
        });

        it('handles mixed data types', function () {
            $user = User::factory()->make(['id' => 1, 'name' => 'John']);

            $data = [
                ['id' => 1, 'name' => 'Plain Array'],
                $user->toArray(), // Array from Eloquent model
                'string_value',
                123,
            ];

            $serialized = Serializer::serializeData($data);

            expect($serialized[0])->toBe(['id' => 1, 'name' => 'Plain Array']);
            expect($serialized[1])->toBeArray();
            expect($serialized[2])->toBe('string_value');
            expect($serialized[3])->toBe(123);
        });

    });

    describe('Pagination Serialization', function () {

        it('serializes pagination with all fields', function () {
            $pagination = [
                'current_page' => 2,
                'per_page' => 10,
                'total' => 50,
                'last_page' => 5,
                'from' => 11,
                'to' => 20,
                'links' => [
                    ['url' => 'http://example.com?page=1', 'label' => 'Previous', 'active' => false],
                    ['url' => 'http://example.com?page=2', 'label' => '2', 'active' => true],
                    ['url' => 'http://example.com?page=3', 'label' => 'Next', 'active' => false],
                ],
            ];

            $serialized = Serializer::serializePagination($pagination);

            expect($serialized['current_page'])->toBe(2);
            expect($serialized['per_page'])->toBe(10);
            expect($serialized['total'])->toBe(50);
            expect($serialized['last_page'])->toBe(5);
            expect($serialized['from'])->toBe(11);
            expect($serialized['to'])->toBe(20);
            expect($serialized['links'])->toBeArray();
        });

        it('applies defaults for missing pagination fields', function () {
            $pagination = []; // Empty pagination

            $serialized = Serializer::serializePagination($pagination);

            expect($serialized['current_page'])->toBe(1);
            expect($serialized['per_page'])->toBe(25);
            expect($serialized['total'])->toBe(0);
            expect($serialized['last_page'])->toBe(1);
            expect($serialized['from'])->toBeNull();
            expect($serialized['to'])->toBeNull();
            expect($serialized['links'])->toBe([]);
        });

        it('serializes pagination links', function () {
            $links = [
                ['url' => 'http://example.com?page=1', 'label' => 'Previous', 'active' => false],
                ['url' => null, 'label' => '...', 'active' => false],
                ['url' => 'http://example.com?page=5', 'label' => '5', 'active' => true],
            ];

            $serialized = Serializer::serializePaginationLinks($links);

            expect($serialized)->toBeArray();
            expect(count($serialized))->toBe(3);

            expect($serialized[0]['url'])->toBe('http://example.com?page=1');
            expect($serialized[0]['label'])->toBe('Previous');
            expect($serialized[0]['active'])->toBeFalse();

            expect($serialized[1]['url'])->toBeNull();
            expect($serialized[2]['active'])->toBeTrue();
        });

    });

    describe('Sort Serialization', function () {

        it('returns sort array unchanged', function () {
            $sort = ['name' => 'desc', 'email' => 'asc'];

            $serialized = Serializer::serializeSort($sort);

            expect($serialized)->toBe($sort);
        });

        it('handles empty sort array', function () {
            $sort = [];

            $serialized = Serializer::serializeSort($sort);

            expect($serialized)->toBe([]);
        });

    });

    describe('Action Serialization', function () {

        beforeEach(function () {
            $this->action = Action::make('edit')->setTableClass('App\\Tables\\UserTable');
        });

        it('serializes Arrayable actions', function () {
            $actions = [$this->action];

            $serialized = Serializer::serializeActions($actions);

            expect($serialized)->toBeArray();
            expect(count($serialized))->toBe(1);
            expect($serialized[0])->toBeArray();
            expect($serialized[0]['name'])->toBe('edit');
        });

        it('passes through non-Arrayable actions', function () {
            $actions = [
                $this->action,
                ['name' => 'custom', 'label' => 'Custom Action'], // Plain array
            ];

            $serialized = Serializer::serializeActions($actions);

            expect($serialized[0])->toBeArray();
            expect($serialized[0]['name'])->toBe('edit');

            expect($serialized[1])->toBe(['name' => 'custom', 'label' => 'Custom Action']);
        });

        it('serializes bulk actions using same logic', function () {
            $bulkActions = [$this->action];

            $serialized = Serializer::serializeBulkActions($bulkActions);

            expect($serialized)->toBeArray();
            expect($serialized[0]['name'])->toBe('edit');
        });

        it('serializes header actions using same logic', function () {
            $headerActions = [$this->action];

            $serialized = Serializer::serializeHeaderActions($headerActions);

            expect($serialized)->toBeArray();
            expect($serialized[0]['name'])->toBe('edit');
        });

    });

    describe('Edge Cases and Error Handling', function () {

        it('handles null values in data', function () {
            $data = [
                ['name' => 'John', 'email' => null],
                null,
                ['name' => null, 'email' => 'jane@example.com'],
            ];

            $serialized = Serializer::serializeData($data);

            expect($serialized[0]['name'])->toBe('John');
            expect($serialized[0]['email'])->toBeNull();
            expect($serialized[1])->toBeNull();
            expect($serialized[2]['name'])->toBeNull();
        });

        it('handles empty arrays gracefully', function () {
            $emptyTableResult = new TableResult(
                config: [],
                data: [],
                pagination: [],
                sort: [],
                search: null,
                name: null,
                actions: [],
                bulkActions: [],
                headerActions: [],
                primaryKey: null,
            );

            $serialized = Serializer::serializeTableResult($emptyTableResult);

            expect($serialized)->toBeArray();
            expect($serialized['data'])->toBe([]);
            expect($serialized['actions'])->toBe([]);
            expect($serialized['search'])->toBeNull();
        });

        it('preserves data types correctly', function () {
            $data = [
                [
                    'id' => 1,
                    'name' => 'John',
                    'active' => true,
                    'score' => 95.5,
                    'tags' => ['php', 'laravel'],
                    'metadata' => null,
                ],
            ];

            $serialized = Serializer::serializeData($data);

            expect($serialized[0]['id'])->toBe(1);
            expect($serialized[0]['name'])->toBe('John');
            expect($serialized[0]['active'])->toBe(true);
            expect($serialized[0]['score'])->toBe(95.5);
            expect($serialized[0]['tags'])->toBe(['php', 'laravel']);
            expect($serialized[0]['metadata'])->toBeNull();
        });

    });

});
