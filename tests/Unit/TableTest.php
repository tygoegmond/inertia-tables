<?php

use Egmond\InertiaTables\Actions\Action;
use Egmond\InertiaTables\Actions\BulkAction;
use Egmond\InertiaTables\Columns\TextColumn;
use Egmond\InertiaTables\Table;
use Egmond\InertiaTables\TableResult;
use Egmond\InertiaTables\Tests\Database\Models\User;

describe('Table Class', function () {

    beforeEach(function () {
        $this->table = new Table;
        $this->userQuery = User::query();
    });

    describe('Basic Configuration', function () {

        it('can be instantiated', function () {
            expect($this->table)->toBeInstanceOf(Table::class);
        });

        it('has default values', function () {
            expect($this->table->getColumns())->toBe([]);
            expect($this->table->getQuery())->toBeNull();
            expect($this->table->getPerPage())->toBe(25);
            expect($this->table->getDefaultSort())->toBe([]);
            expect($this->table->isSearchable())->toBeFalse();
            expect($this->table->getName())->toBeNull();
            expect($this->table->getActions())->toBe([]);
            expect($this->table->getBulkActions())->toBe([]);
            expect($this->table->getHeaderActions())->toBe([]);
            expect($this->table->getTableClass())->toBeNull();
        });

    });

    describe('Fluent Interface', function () {

        it('can set query fluently', function () {
            $result = $this->table->query($this->userQuery);

            expect($result)->toBe($this->table);
            expect($this->table->getQuery())->toBe($this->userQuery);
        });

        it('can set columns fluently', function () {
            $columns = [
                TextColumn::make('name'),
                TextColumn::make('email'),
            ];

            $result = $this->table->columns($columns);

            expect($result)->toBe($this->table);
            expect($this->table->getColumns())->toBe($columns);
        });

        it('can set pagination fluently', function () {
            $result = $this->table->paginate(50);

            expect($result)->toBe($this->table);
            expect($this->table->getPerPage())->toBe(50);
        });

        it('can set searchable fluently', function () {
            $result = $this->table->searchable();

            expect($result)->toBe($this->table);
            expect($this->table->isSearchable())->toBeTrue();
        });

        it('can set searchable with explicit boolean', function () {
            $result = $this->table->searchable(false);

            expect($result)->toBe($this->table);
            expect($this->table->isSearchable())->toBeFalse();
        });

        it('can set default sort fluently', function () {
            $result = $this->table->defaultSort('name', 'desc');

            expect($result)->toBe($this->table);
            expect($this->table->getDefaultSort())->toBe(['name' => 'desc']);
        });

        it('can set multiple default sorts', function () {
            $this->table
                ->defaultSort('name', 'desc')
                ->defaultSort('email', 'asc');

            expect($this->table->getDefaultSort())->toBe([
                'name' => 'desc',
                'email' => 'asc',
            ]);
        });

        it('defaults sort direction to asc', function () {
            $this->table->defaultSort('name');

            expect($this->table->getDefaultSort())->toBe(['name' => 'asc']);
        });

        it('can set table name fluently with as method', function () {
            $result = $this->table->as('users');

            expect($result)->toBe($this->table);
            expect($this->table->getName())->toBe('users');
        });

        it('can set table name fluently with setName method', function () {
            $result = $this->table->setName('users');

            expect($result)->toBe($this->table);
            expect($this->table->getName())->toBe('users');
        });

        it('can set table class fluently', function () {
            $result = $this->table->setTableClass('App\\Tables\\UserTable');

            expect($result)->toBe($this->table);
            expect($this->table->getTableClass())->toBe('App\\Tables\\UserTable');
        });

    });

    describe('Actions Configuration', function () {

        it('can set actions fluently', function () {
            $actions = [
                Action::make('edit'),
                Action::make('delete'),
            ];

            $result = $this->table->actions($actions);

            expect($result)->toBe($this->table);
            expect($this->table->getActions())->toBe($actions);
        });

        it('can set bulk actions fluently', function () {
            $bulkActions = [
                BulkAction::make('delete'),
                BulkAction::make('archive'),
            ];

            $result = $this->table->bulkActions($bulkActions);

            expect($result)->toBe($this->table);
            expect($this->table->getBulkActions())->toBe($bulkActions);
        });

        it('can set header actions fluently', function () {
            $headerActions = [
                Action::make('create'),
                Action::make('export'),
            ];

            $result = $this->table->headerActions($headerActions);

            expect($result)->toBe($this->table);
            expect($this->table->getHeaderActions())->toBe($headerActions);
        });

        it('can check if table has actions', function () {
            expect($this->table->hasActions())->toBeFalse();

            $this->table->actions([Action::make('edit')]);

            expect($this->table->hasActions())->toBeTrue();
        });

        it('can check if table has bulk actions', function () {
            expect($this->table->hasBulkActions())->toBeFalse();

            $this->table->bulkActions([BulkAction::make('delete')]);

            expect($this->table->hasBulkActions())->toBeTrue();
        });

        it('can check if table has header actions', function () {
            expect($this->table->hasHeaderActions())->toBeFalse();

            $this->table->headerActions([Action::make('create')]);

            expect($this->table->hasHeaderActions())->toBeTrue();
        });

    });

    describe('Method Chaining', function () {

        it('can chain multiple methods', function () {
            $columns = [TextColumn::make('name'), TextColumn::make('email')];
            $actions = [Action::make('edit')];

            $result = $this->table
                ->query($this->userQuery)
                ->columns($columns)
                ->paginate(50)
                ->searchable()
                ->defaultSort('name', 'desc')
                ->as('users')
                ->actions($actions)
                ->setTableClass('App\\Tables\\UserTable');

            expect($result)->toBe($this->table);
            expect($this->table->getQuery())->toBe($this->userQuery);
            expect($this->table->getColumns())->toBe($columns);
            expect($this->table->getPerPage())->toBe(50);
            expect($this->table->isSearchable())->toBeTrue();
            expect($this->table->getDefaultSort())->toBe(['name' => 'desc']);
            expect($this->table->getName())->toBe('users');
            expect($this->table->getActions())->toBe($actions);
            expect($this->table->getTableClass())->toBe('App\\Tables\\UserTable');
        });

    });

    describe('Build Method', function () {

        it('throws exception when query is not set', function () {
            $this->table->as('users');

            expect(fn () => $this->table->build())
                ->toThrow(Exception::class, 'Query is required. Use query() method to set the query.');
        });

        it('throws exception when name is not set', function () {
            $this->table->query($this->userQuery);

            expect(fn () => $this->table->build())
                ->toThrow(Exception::class, 'Table name is required. Use as() method to set the table name.');
        });

        it('builds successfully with required parameters', function () {
            User::factory()->create();

            $result = $this->table
                ->query($this->userQuery)
                ->as('users')
                ->build();

            expect($result)->toBeInstanceOf(TableResult::class);
        });

        it('passes configuration to TableBuilder', function () {
            User::factory()->create();

            $columns = [TextColumn::make('name')];
            $actions = [Action::make('edit')];

            $result = $this->table
                ->query($this->userQuery)
                ->columns($columns)
                ->paginate(50)
                ->searchable()
                ->defaultSort('name', 'desc')
                ->as('users')
                ->actions($actions)
                ->setTableClass('App\\Tables\\UserTable')
                ->build();

            expect($result)->toBeInstanceOf(TableResult::class);
            expect($result->name)->toBe('users');
        });

    });

    describe('Edge Cases', function () {

        it('handles empty arrays for actions', function () {
            $this->table->actions([]);
            $this->table->bulkActions([]);
            $this->table->headerActions([]);

            expect($this->table->getActions())->toBe([]);
            expect($this->table->getBulkActions())->toBe([]);
            expect($this->table->getHeaderActions())->toBe([]);
            expect($this->table->hasActions())->toBeFalse();
            expect($this->table->hasBulkActions())->toBeFalse();
            expect($this->table->hasHeaderActions())->toBeFalse();
        });

        it('handles pagination with edge values', function () {
            $this->table->paginate(1);
            expect($this->table->getPerPage())->toBe(1);

            $this->table->paginate(1000);
            expect($this->table->getPerPage())->toBe(1000);
        });

        it('overwrites previous configurations', function () {
            $this->table
                ->paginate(25)
                ->paginate(50);

            expect($this->table->getPerPage())->toBe(50);

            $this->table
                ->searchable(true)
                ->searchable(false);

            expect($this->table->isSearchable())->toBeFalse();

            $this->table
                ->as('users')
                ->as('posts');

            expect($this->table->getName())->toBe('posts');
        });

    });

});
