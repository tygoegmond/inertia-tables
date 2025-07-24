<?php

use Egmond\InertiaTables\TableResult;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

describe('TableResult Class', function () {
    
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
            'links' => [],
        ];

        $this->sort = ['name' => 'asc'];
        $this->search = 'john';
        $this->name = 'users';
        $this->actions = [
            ['name' => 'edit', 'label' => 'Edit'],
        ];
        $this->bulkActions = [
            ['name' => 'delete', 'label' => 'Delete'],
        ];
        $this->headerActions = [
            ['name' => 'create', 'label' => 'Create'],
        ];
        $this->primaryKey = 'id';

        $this->tableResult = new TableResult(
            config: $this->config,
            data: $this->data,
            pagination: $this->pagination,
            sort: $this->sort,
            search: $this->search,
            name: $this->name,
            actions: $this->actions,
            bulkActions: $this->bulkActions,
            headerActions: $this->headerActions,
            primaryKey: $this->primaryKey,
        );
    });

    describe('Instantiation', function () {
        
        it('can be instantiated with required parameters', function () {
            $result = new TableResult(
                config: $this->config,
                data: $this->data,
                pagination: $this->pagination,
            );
            
            expect($result)->toBeInstanceOf(TableResult::class);
            expect($result->config)->toBe($this->config);
            expect($result->data)->toBe($this->data);
            expect($result->pagination)->toBe($this->pagination);
        });

        it('can be instantiated with all parameters', function () {
            expect($this->tableResult)->toBeInstanceOf(TableResult::class);
            expect($this->tableResult->config)->toBe($this->config);
            expect($this->tableResult->data)->toBe($this->data);
            expect($this->tableResult->pagination)->toBe($this->pagination);
            expect($this->tableResult->sort)->toBe($this->sort);
            expect($this->tableResult->search)->toBe($this->search);
            expect($this->tableResult->name)->toBe($this->name);
            expect($this->tableResult->actions)->toBe($this->actions);
            expect($this->tableResult->bulkActions)->toBe($this->bulkActions);
            expect($this->tableResult->headerActions)->toBe($this->headerActions);
            expect($this->tableResult->primaryKey)->toBe($this->primaryKey);
        });

        it('has default values for optional parameters', function () {
            $result = new TableResult(
                config: $this->config,
                data: $this->data,
                pagination: $this->pagination,
            );
            
            expect($result->sort)->toBe([]);
            expect($result->search)->toBeNull();
            expect($result->name)->toBeNull();
            expect($result->actions)->toBe([]);
            expect($result->bulkActions)->toBe([]);
            expect($result->headerActions)->toBe([]);
            expect($result->primaryKey)->toBeNull();
        });

    });

    describe('Interface Implementation', function () {
        
        it('implements Arrayable interface', function () {
            expect($this->tableResult)->toBeInstanceOf(Arrayable::class);
        });

        it('implements Jsonable interface', function () {
            expect($this->tableResult)->toBeInstanceOf(Jsonable::class);
        });

        it('implements JsonSerializable interface', function () {
            expect($this->tableResult)->toBeInstanceOf(JsonSerializable::class);
        });

    });

    describe('Serialization Methods', function () {
        
        it('converts to array correctly', function () {
            $array = $this->tableResult->toArray();
            
            expect($array)->toBeArray();
            expect($array)->toHaveKeys([
                'config',
                'data',
                'pagination',
                'sort',
                'search',
                'name',
                'actions',
                'bulkActions',
                'headerActions',
                'primaryKey',
            ]);
        });

        it('converts to JSON correctly', function () {
            $json = $this->tableResult->toJson();
            
            expect($json)->toBeString();
            
            $decoded = json_decode($json, true);
            expect($decoded)->toHaveKeys([
                'config',
                'data',
                'pagination',
                'sort',
                'search',
                'name',
                'actions',
                'bulkActions',
                'headerActions',
                'primaryKey',
            ]);
        });

        it('converts to JSON with options', function () {
            $json = $this->tableResult->toJson(JSON_PRETTY_PRINT);
            
            expect($json)->toBeString();
            expect($json)->toContain("{\n");
        });

        it('implements jsonSerialize correctly', function () {
            $serialized = $this->tableResult->jsonSerialize();
            
            expect($serialized)->toBeArray();
            expect($serialized)->toHaveKeys([
                'config',
                'data',
                'pagination',
                'sort',
                'search',
                'name',
                'actions',
                'bulkActions',
                'headerActions',
                'primaryKey',
            ]);
        });

    });

    describe('Property Access', function () {
        
        it('provides readonly access to all properties', function () {
            expect($this->tableResult->config)->toBe($this->config);
            expect($this->tableResult->data)->toBe($this->data);
            expect($this->tableResult->pagination)->toBe($this->pagination);
            expect($this->tableResult->sort)->toBe($this->sort);
            expect($this->tableResult->search)->toBe($this->search);
            expect($this->tableResult->name)->toBe($this->name);
            expect($this->tableResult->actions)->toBe($this->actions);
            expect($this->tableResult->bulkActions)->toBe($this->bulkActions);
            expect($this->tableResult->headerActions)->toBe($this->headerActions);
            expect($this->tableResult->primaryKey)->toBe($this->primaryKey);
        });

    });

    describe('Serialization Consistency', function () {
        
        it('toArray and jsonSerialize return same result', function () {
            $array = $this->tableResult->toArray();
            $jsonSerialized = $this->tableResult->jsonSerialize();
            
            expect($array)->toBe($jsonSerialized);
        });

        it('JSON encoding and toJson return equivalent data', function () {
            $jsonFromMethod = $this->tableResult->toJson();
            $jsonFromFunction = json_encode($this->tableResult);
            
            $decodedMethod = json_decode($jsonFromMethod, true);
            $decodedFunction = json_decode($jsonFromFunction, true);
            
            expect($decodedMethod)->toBe($decodedFunction);
        });

    });

    describe('Empty and Null Values', function () {
        
        it('handles empty arrays correctly', function () {
            $result = new TableResult(
                config: [],
                data: [],
                pagination: [],
                sort: [],
                actions: [],
                bulkActions: [],
                headerActions: [],
            );
            
            $array = $result->toArray();
            
            // Config gets normalized with defaults by the Serializer
            expect($array['config'])->toBe([
                'columns' => [],
                'searchable' => false,
                'perPage' => 25,
                'defaultSort' => [],
            ]);
            expect($array['data'])->toBe([]);
            // Pagination gets normalized with defaults by the Serializer
            expect($array['pagination'])->toBe([
                'current_page' => 1,
                'per_page' => 25,
                'total' => 0,
                'last_page' => 1,
                'from' => null,
                'to' => null,
                'links' => [],
            ]);
            expect($array['sort'])->toBe([]);
            expect($array['actions'])->toBe([]);
            expect($array['bulkActions'])->toBe([]);
            expect($array['headerActions'])->toBe([]);
        });

        it('handles null values correctly', function () {
            $result = new TableResult(
                config: $this->config,
                data: $this->data,
                pagination: $this->pagination,
                search: null,
                name: null,
                primaryKey: null,
            );
            
            $array = $result->toArray();
            
            expect($array['search'])->toBeNull();
            expect($array['name'])->toBeNull();
            expect($array['primaryKey'])->toBeNull();
        });

    });

    describe('Data Integrity', function () {
        
        it('preserves complex nested data structures', function () {
            $complexConfig = [
                'columns' => [
                    [
                        'key' => 'user.name',
                        'label' => 'User Name',
                        'type' => 'text',
                        'meta' => ['searchable' => true, 'sortable' => false],
                    ],
                ],
                'searchable' => true,
                'perPage' => 50,
                'defaultSort' => ['user.name' => 'asc'],
            ];

            $complexData = [
                [
                    'id' => 1,
                    'user' => ['name' => 'John', 'email' => 'john@example.com'],
                    'meta' => ['created_at' => '2023-01-01', 'updated_at' => '2023-01-02'],
                ],
            ];

            $result = new TableResult(
                config: $complexConfig,
                data: $complexData,
                pagination: $this->pagination,
            );

            $array = $result->toArray();
            
            // Config gets normalized by Serializer but preserves the data we set
            expect($array['config']['columns'])->toBe($complexConfig['columns']);
            expect($array['config']['searchable'])->toBe(true);
            expect($array['config']['perPage'])->toBe(50);
            expect($array['config']['defaultSort'])->toBe(['user.name' => 'asc']);
            expect($array['data'])->toBe($complexData);
        });

        it('maintains data types in serialization', function () {
            $typedData = [
                [
                    'id' => 1,
                    'name' => 'John',
                    'active' => true,
                    'score' => 95.5,
                    'tags' => ['php', 'laravel'],
                    'metadata' => null,
                ],
            ];

            $result = new TableResult(
                config: $this->config,
                data: $typedData,
                pagination: $this->pagination,
            );

            $json = $result->toJson();
            $decoded = json_decode($json, true);
            
            expect($decoded['data'][0]['id'])->toBe(1);
            expect($decoded['data'][0]['name'])->toBe('John');
            expect($decoded['data'][0]['active'])->toBe(true);
            expect($decoded['data'][0]['score'])->toBe(95.5);
            expect($decoded['data'][0]['tags'])->toBe(['php', 'laravel']);
            expect($decoded['data'][0]['metadata'])->toBeNull();
        });

    });

    describe('Large Data Handling', function () {
        
        it('handles large datasets efficiently', function () {
            $largeData = [];
            for ($i = 1; $i <= 1000; $i++) {
                $largeData[] = [
                    'id' => $i,
                    'name' => "User {$i}",
                    'email' => "user{$i}@example.com",
                ];
            }

            $result = new TableResult(
                config: $this->config,
                data: $largeData,
                pagination: array_merge($this->pagination, ['total' => 1000]),
            );

            $array = $result->toArray();
            
            expect(count($array['data']))->toBe(1000);
            expect($array['pagination']['total'])->toBe(1000);
            
            $json = $result->toJson();
            expect($json)->toBeString();
            expect(strlen($json))->toBeGreaterThan(10000);
        });

    });

});