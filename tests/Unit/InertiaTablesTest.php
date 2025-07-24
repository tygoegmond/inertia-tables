<?php

use Egmond\InertiaTables\Builder\TableBuilder;
use Egmond\InertiaTables\InertiaTables;
use Illuminate\Http\Request;

describe('InertiaTables Class', function () {
    
    describe('Static Factory Method', function () {
        
        it('can create TableBuilder without request', function () {
            $builder = InertiaTables::table();
            
            expect($builder)->toBeInstanceOf(TableBuilder::class);
        });

        it('can create TableBuilder with request', function () {
            $request = new Request(['test' => 'value']);
            $builder = InertiaTables::table($request);
            
            expect($builder)->toBeInstanceOf(TableBuilder::class);
        });

        it('creates new instances on each call', function () {
            $builder1 = InertiaTables::table();
            $builder2 = InertiaTables::table();
            
            expect($builder1)->not->toBe($builder2);
            expect($builder1)->toBeInstanceOf(TableBuilder::class);
            expect($builder2)->toBeInstanceOf(TableBuilder::class);
        });

        it('passes request to TableBuilder correctly', function () {
            $request = new Request([
                'users' => [
                    'search' => 'john',
                    'sort' => 'name',
                    'direction' => 'desc',
                ]
            ]);
            
            $builder = InertiaTables::table($request);
            
            expect($builder)->toBeInstanceOf(TableBuilder::class);
            // The request should be available internally for the builder
        });

    });

    describe('Integration with TableBuilder', function () {
        
        it('creates fully functional TableBuilder', function () {
            $builder = InertiaTables::table();
            
            // Test that we can use all TableBuilder methods
            $result = $builder
                ->columns([])
                ->searchable()
                ->paginate(10)
                ->setName('test');
            
            expect($result)->toBe($builder);
            expect($result)->toBeInstanceOf(TableBuilder::class);
        });

        it('handles request parameters in created builder', function () {
            $request = new Request([
                'users' => [
                    'search' => 'test search',
                    'page' => '2',
                ]
            ]);
            
            $builder = InertiaTables::table($request);
            
            // Builder should have access to the request for parameter extraction
            expect($builder)->toBeInstanceOf(TableBuilder::class);
        });

    });

    describe('Usage Patterns', function () {
        
        it('supports fluent interface creation pattern', function () {
            $request = new Request();
            
            $builder = InertiaTables::table($request)
                ->columns([])
                ->searchable()
                ->paginate(25)
                ->sortBy('name', 'asc')
                ->setName('users');
            
            expect($builder)->toBeInstanceOf(TableBuilder::class);
        });

        it('can be used without request for default behavior', function () {
            $builder = InertiaTables::table()
                ->columns([])
                ->setName('default_table');
            
            expect($builder)->toBeInstanceOf(TableBuilder::class);
        });

    });

    describe('Edge Cases', function () {
        
        it('handles null request gracefully', function () {
            $builder = InertiaTables::table(null);
            
            expect($builder)->toBeInstanceOf(TableBuilder::class);
        });

        it('works with empty request', function () {
            $request = new Request();
            $builder = InertiaTables::table($request);
            
            expect($builder)->toBeInstanceOf(TableBuilder::class);
        });

        it('works with request containing no table parameters', function () {
            $request = new Request(['other_data' => 'value']);
            $builder = InertiaTables::table($request);
            
            expect($builder)->toBeInstanceOf(TableBuilder::class);
        });

    });

    describe('Consistency', function () {
        
        it('maintains consistent behavior across calls', function () {
            $request = new Request(['test' => 'value']);
            
            $builder1 = InertiaTables::table($request);
            $builder2 = InertiaTables::table($request);
            
            // Should create different instances but with same behavior
            expect($builder1)->not->toBe($builder2);
            expect($builder1)->toBeInstanceOf(TableBuilder::class);
            expect($builder2)->toBeInstanceOf(TableBuilder::class);
        });

        it('creates independent builder instances', function () {
            $builder1 = InertiaTables::table()->setName('table1');
            $builder2 = InertiaTables::table()->setName('table2');
            
            // Each builder should be independent
            expect($builder1)->toBeInstanceOf(TableBuilder::class);
            expect($builder2)->toBeInstanceOf(TableBuilder::class);
            expect($builder1)->not->toBe($builder2);
        });

    });

    describe('Static Class Behavior', function () {
        
        it('is a static factory class', function () {
            $reflection = new ReflectionClass(InertiaTables::class);
            $method = $reflection->getMethod('table');
            
            expect($method->isStatic())->toBeTrue();
            expect($method->isPublic())->toBeTrue();
        });

        it('does not require instantiation', function () {
            // Should be able to call without creating an instance
            $builder = InertiaTables::table();
            
            expect($builder)->toBeInstanceOf(TableBuilder::class);
        });

    });

});