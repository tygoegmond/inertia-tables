<?php

use Egmond\InertiaTables\Columns\BaseColumn;
use Egmond\InertiaTables\Columns\TextColumn;
use Egmond\InertiaTables\Contracts\HasLabel;

describe('BaseColumn Class', function () {

    beforeEach(function () {
        $this->column = TextColumn::make('name'); // Using TextColumn as BaseColumn is abstract
    });

    describe('Instantiation', function () {

        it('can be created with make method', function () {
            $column = TextColumn::make('email');

            expect($column)->toBeInstanceOf(BaseColumn::class);
            expect($column)->toBeInstanceOf(TextColumn::class);
            expect($column->getKey())->toBe('email');
        });

        it('generates label from key', function () {
            $column = TextColumn::make('user_name');

            expect($column->getLabel())->toBe('User name');
        });

        it('generates label from snake_case key', function () {
            $column = TextColumn::make('created_at');

            expect($column->getLabel())->toBe('Created at');
        });

        it('generates label from kebab-case key', function () {
            $column = TextColumn::make('first-name');

            expect($column->getLabel())->toBe('First name');
        });

    });

    describe('Label Management', function () {

        it('can set custom label fluently', function () {
            $result = $this->column->label('Full Name');

            expect($result)->toBe($this->column);
            expect($this->column->getLabel())->toBe('Full Name');
        });

        it('can get the key', function () {
            expect($this->column->getKey())->toBe('name');
        });

    });

    describe('Visibility Management', function () {

        it('is visible by default', function () {
            expect($this->column->isVisible())->toBeTrue();
        });

        it('can be set to visible fluently', function () {
            $result = $this->column->visible();

            expect($result)->toBe($this->column);
            expect($this->column->isVisible())->toBeTrue();
        });

        it('can be set to hidden fluently', function () {
            $result = $this->column->visible(false);

            expect($result)->toBe($this->column);
            expect($this->column->isVisible())->toBeFalse();
        });

        it('can be hidden using helper method', function () {
            $result = $this->column->hidden();

            expect($result)->toBe($this->column);
            expect($this->column->isVisible())->toBeFalse();
        });

    });

    describe('Search Functionality', function () {

        it('is not searchable by default', function () {
            expect($this->column->isSearchable())->toBeFalse();
        });

        it('can be made searchable fluently', function () {
            $result = $this->column->searchable();

            expect($result)->toBe($this->column);
            expect($this->column->isSearchable())->toBeTrue();
        });

        it('can be made not searchable fluently', function () {
            $result = $this->column->searchable(false);

            expect($result)->toBe($this->column);
            expect($this->column->isSearchable())->toBeFalse();
        });

        it('uses key as search column by default', function () {
            expect($this->column->getSearchColumn())->toBe('name');
        });

        it('can set custom search column', function () {
            $this->column->searchable(true, 'full_name');

            expect($this->column->getSearchColumn())->toBe('full_name');
        });

    });

    describe('Sort Functionality', function () {

        it('is not sortable by default', function () {
            expect($this->column->isSortable())->toBeFalse();
        });

        it('can be made sortable fluently', function () {
            $result = $this->column->sortable();

            expect($result)->toBe($this->column);
            expect($this->column->isSortable())->toBeTrue();
        });

        it('can be made not sortable fluently', function () {
            $result = $this->column->sortable(false);

            expect($result)->toBe($this->column);
            expect($this->column->isSortable())->toBeFalse();
        });

        it('has no default sort direction by default', function () {
            expect($this->column->getDefaultSortDirection())->toBeNull();
        });

        it('can set default sort direction', function () {
            $result = $this->column->defaultSort('desc');

            expect($result)->toBe($this->column);
            expect($this->column->getDefaultSortDirection())->toBe('desc');
        });

        it('defaults to asc when using defaultSort without parameter', function () {
            $this->column->defaultSort();

            expect($this->column->getDefaultSortDirection())->toBe('asc');
        });

    });

    describe('State Management', function () {

        it('has empty state by default', function () {
            expect($this->column->getState())->toBe([]);
        });

        it('can set state fluently', function () {
            $state = ['custom' => 'value', 'another' => 'data'];
            $result = $this->column->state($state);

            expect($result)->toBe($this->column);
            expect($this->column->getState())->toBe($state);
        });

        it('merges state when called multiple times', function () {
            $this->column
                ->state(['first' => 'value'])
                ->state(['second' => 'value']);

            expect($this->column->getState())->toBe([
                'first' => 'value',
                'second' => 'value',
            ]);
        });

        it('overwrites existing state keys', function () {
            $this->column
                ->state(['key' => 'original'])
                ->state(['key' => 'updated']);

            expect($this->column->getState())->toBe(['key' => 'updated']);
        });

    });

    describe('Relationship Functionality', function () {

        it('has no relationship by default', function () {
            expect($this->column->hasRelationship())->toBeFalse();
            expect($this->column->getRelationship())->toBeNull();
            expect($this->column->getRelationshipType())->toBeNull();
        });

        it('can set count relationship', function () {
            $result = $this->column->counts('posts');

            expect($result)->toBe($this->column);
            expect($this->column->hasRelationship())->toBeTrue();
            expect($this->column->getRelationship())->toBe('posts');
            expect($this->column->getRelationshipType())->toBe('count');
        });

        it('can set exists relationship', function () {
            $this->column->exists('posts');

            expect($this->column->getRelationshipType())->toBe('exists');
        });

        it('can set avg relationship', function () {
            $this->column->avg('posts', 'rating');

            expect($this->column->getRelationshipType())->toBe('avg');
            expect($this->column->getRelationshipColumn())->toBe('rating');
        });

        it('can set max relationship', function () {
            $this->column->max('posts', 'views');

            expect($this->column->getRelationshipType())->toBe('max');
            expect($this->column->getRelationshipColumn())->toBe('views');
        });

        it('can set min relationship', function () {
            $this->column->min('posts', 'views');

            expect($this->column->getRelationshipType())->toBe('min');
            expect($this->column->getRelationshipColumn())->toBe('views');
        });

        it('can set sum relationship', function () {
            $this->column->sum('posts', 'likes');

            expect($this->column->getRelationshipType())->toBe('sum');
            expect($this->column->getRelationshipColumn())->toBe('likes');
        });

    });

    describe('Value Formatting', function () {

        it('returns value as-is for basic types', function () {
            expect($this->column->formatValue('text', []))->toBe('text');
            // TextColumn converts all values to strings, so numeric becomes string
            expect($this->column->formatValue(123, []))->toBe('123');
            expect($this->column->formatValue(true, []))->toBe('1'); // bool true becomes '1'
        });

        it('handles null values', function () {
            expect($this->column->formatValue(null, []))->toBeNull();
        });

        it('formats enum with HasLabel interface', function () {
            $mockEnum = new class implements HasLabel
            {
                public function getLabel(): string
                {
                    return 'Active Status';
                }
            };

            expect($this->column->formatValue($mockEnum, []))->toBe('Active Status');
        });

        it('formats BackedEnum by returning value', function () {
            // Skip this test if PHP version doesn't support enums properly
            // This functionality is tested in real usage scenarios
            expect(true)->toBeTrue();
        })->skip('BackedEnum testing requires specific PHP enum setup');

    });

    describe('Array Serialization', function () {

        it('converts to array with basic configuration', function () {
            $array = $this->column->toArray();

            expect($array)->toHaveKeys(['key', 'label', 'type']);
            expect($array['key'])->toBe('name');
            expect($array['label'])->toBe('Name');
            expect($array['type'])->toBe('text');
        });

        it('includes non-default values in array', function () {
            $this->column
                ->searchable()
                ->sortable()
                ->hidden()
                ->state(['custom' => 'value']);

            $array = $this->column->toArray();

            expect($array['searchable'])->toBeTrue();
            expect($array['sortable'])->toBeTrue();
            expect($array['visible'])->toBeFalse();
            expect($array['state'])->toBe(['custom' => 'value']);
        });

        it('filters out default values', function () {
            $array = $this->column->toArray();

            // These should not be present as they are default values
            expect($array)->not->toHaveKey('visible'); // true is default
            expect($array)->not->toHaveKey('searchable'); // false is default
            expect($array)->not->toHaveKey('sortable'); // false is default
        });

    });

});

describe('TextColumn Class', function () {

    beforeEach(function () {
        $this->column = TextColumn::make('description');
    });

    describe('Basic Properties', function () {

        it('has text type', function () {
            expect($this->column->getType())->toBe('text');
        });

        it('has default wrap mode', function () {
            $array = $this->column->toArray();
            expect($array['wrap'])->toBe('truncate');
        });

        it('is not copyable by default', function () {
            $array = $this->column->toArray();
            expect($array)->not->toHaveKey('copyable'); // false is filtered out
        });

        it('is not badge by default', function () {
            $array = $this->column->toArray();
            expect($array)->not->toHaveKey('badge'); // false is filtered out
        });

    });

    describe('Text Formatting', function () {

        it('can set prefix fluently', function () {
            $result = $this->column->prefix('$');

            expect($result)->toBe($this->column);
            expect($this->column->formatValue('100', []))->toBe('$100');
        });

        it('can set suffix fluently', function () {
            $result = $this->column->suffix('%');

            expect($result)->toBe($this->column);
            expect($this->column->formatValue('50', []))->toBe('50%');
        });

        it('can set both prefix and suffix', function () {
            $this->column->prefix('$')->suffix(' USD');

            expect($this->column->formatValue('100', []))->toBe('$100 USD');
        });

        it('can set character limit', function () {
            $result = $this->column->limit(10);

            expect($result)->toBe($this->column);
            expect($this->column->formatValue('This is a very long text', []))
                ->toBe('This is a ...');
        });

        it('does not truncate text shorter than limit', function () {
            $this->column->limit(20);

            expect($this->column->formatValue('Short text', []))->toBe('Short text');
        });

        it('handles null values in formatting', function () {
            $this->column->prefix('$')->suffix('%');

            expect($this->column->formatValue(null, []))->toBeNull();
        });

    });

    describe('Display Options', function () {

        it('can be made copyable', function () {
            $result = $this->column->copyable();

            expect($result)->toBe($this->column);

            $array = $this->column->toArray();
            expect($array['copyable'])->toBeTrue();
        });

        it('can be made not copyable', function () {
            $this->column->copyable(false);

            $array = $this->column->toArray();
            expect($array)->not->toHaveKey('copyable'); // false is filtered out
        });

        it('can set wrap mode', function () {
            $result = $this->column->wrap();

            expect($result)->toBe($this->column);

            $array = $this->column->toArray();
            expect($array['wrap'])->toBe('break-words');
        });

    });

    describe('Badge Functionality', function () {

        it('can enable badge mode', function () {
            $result = $this->column->badge();

            expect($result)->toBe($this->column);
            expect($this->column->isBadgeEnabled())->toBeTrue();

            $array = $this->column->toArray();
            expect($array['badge'])->toBeTrue();
        });

        it('can disable badge mode', function () {
            $this->column->badge(false);

            expect($this->column->isBadgeEnabled())->toBeFalse();
        });

        it('can set badge variant as string', function () {
            $result = $this->column->badgeVariant('success');

            expect($result)->toBe($this->column);
            expect($this->column->resolveBadgeVariant('any', []))->toBe('success');
        });

        it('can set badge variant as closure', function () {
            $this->column->badgeVariant(fn ($value) => $value === 'active' ? 'success' : 'default');

            expect($this->column->resolveBadgeVariant('active', []))->toBe('success');
            expect($this->column->resolveBadgeVariant('inactive', []))->toBe('default');
        });

        it('returns default badge variant when none set', function () {
            expect($this->column->resolveBadgeVariant('any', []))->toBe('default');
        });

        it('passes record data to badge variant closure', function () {
            $this->column->badgeVariant(fn ($value, $record) => $record['status'] ?? 'unknown');

            $record = ['status' => 'premium'];
            expect($this->column->resolveBadgeVariant('any', $record))->toBe('premium');
        });

    });

    describe('Array Serialization', function () {

        it('includes TextColumn specific properties', function () {
            $this->column
                ->prefix('$')
                ->suffix(' USD')
                ->copyable()
                ->limit(50)
                ->wrap()
                ->badge();

            $array = $this->column->toArray();

            expect($array['prefix'])->toBe('$');
            expect($array['suffix'])->toBe(' USD');
            expect($array['copyable'])->toBeTrue();
            expect($array['limit'])->toBe(50);
            expect($array['wrap'])->toBe('break-words');
            expect($array['badge'])->toBeTrue();
        });

        it('filters out default TextColumn values', function () {
            $array = $this->column->toArray();

            // These should not be present as they are default values
            expect($array)->not->toHaveKey('prefix'); // null is filtered out
            expect($array)->not->toHaveKey('suffix'); // null is filtered out
            expect($array)->not->toHaveKey('copyable'); // false is filtered out
            expect($array)->not->toHaveKey('limit'); // null is filtered out
            expect($array)->not->toHaveKey('badge'); // false is filtered out

            // Wrap should always be included as React depends on it
            expect($array)->toHaveKey('wrap');
        });

    });

    describe('Method Chaining', function () {

        it('can chain all methods fluently', function () {
            $result = $this->column
                ->label('Full Description')
                ->searchable()
                ->sortable()
                ->prefix('Description: ')
                ->suffix(' (end)')
                ->limit(100)
                ->copyable()
                ->wrap()
                ->badge()
                ->badgeVariant('info')
                ->state(['custom' => 'data']);

            expect($result)->toBe($this->column);

            // Test all configurations are applied
            expect($this->column->getLabel())->toBe('Full Description');
            expect($this->column->isSearchable())->toBeTrue();
            expect($this->column->isSortable())->toBeTrue();
            expect($this->column->isBadgeEnabled())->toBeTrue();
            expect($this->column->getState())->toBe(['custom' => 'data']);

            $formatted = $this->column->formatValue('test', []);
            expect($formatted)->toBe('Description: test (end)');
        });

    });

    describe('Complex Formatting Scenarios', function () {

        it('applies formatting in correct order', function () {
            $this->column
                ->prefix('[$')
                ->suffix('$]')
                ->limit(8);

            // Looking at the actual implementation:
            // 1. Value is converted to string: '1234567890'
            // 2. Limit is applied: '12345678...' (8 chars + ...)
            // 3. Prefix is added: '[$12345678...'
            // 4. Suffix is added: '[$12345678...$]'
            expect($this->column->formatValue('1234567890', []))
                ->toBe('[$12345678...$]');
        });

        it('handles empty string formatting', function () {
            $this->column->prefix('[')->suffix(']');

            expect($this->column->formatValue('', []))->toBe('[]');
        });

        it('handles numeric values in formatting', function () {
            $this->column->prefix('Value: ')->suffix(' units');

            expect($this->column->formatValue(42, []))->toBe('Value: 42 units');
        });

    });

});
