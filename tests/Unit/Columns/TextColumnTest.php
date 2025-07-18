<?php

namespace Egmond\InertiaTables\Tests\Unit\Columns;

use Egmond\InertiaTables\Columns\TextColumn;
use Egmond\InertiaTables\Tests\TestCase;

class TextColumnTest extends TestCase
{
    /** @test */
    public function it_can_create_a_text_column(): void
    {
        $column = TextColumn::make('name');

        $this->assertInstanceOf(TextColumn::class, $column);
        $this->assertEquals('name', $column->getKey());
        $this->assertEquals('Name', $column->getLabel());
        $this->assertEquals('text', $column->getType());
    }

    /** @test */
    public function it_can_be_sortable(): void
    {
        $column = TextColumn::make('name')->sortable();

        $this->assertTrue($column->isSortable());
    }

    /** @test */
    public function it_can_be_searchable(): void
    {
        $column = TextColumn::make('name')->searchable();

        $this->assertTrue($column->isSearchable());
    }

    /** @test */
    public function it_can_have_prefix_and_suffix(): void
    {
        $column = TextColumn::make('name')
            ->prefix('Mr. ')
            ->suffix(' Jr.');

        $value = $column->formatValue('John Doe', []);

        $this->assertEquals('Mr. John Doe Jr.', $value);
    }

    /** @test */
    public function it_can_limit_characters(): void
    {
        $column = TextColumn::make('description')->limit(10);

        $value = $column->formatValue('This is a very long description', []);

        $this->assertEquals('This is a ...', $value);
    }

    /** @test */
    public function it_can_serialize_to_array(): void
    {
        $column = TextColumn::make('name')
            ->sortable()
            ->searchable()
            ->copyable()
            ->prefix('Mr. ')
            ->suffix(' Jr.');

        $array = $column->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('name', $array['key']);
        $this->assertEquals('Name', $array['label']);
        $this->assertEquals('text', $array['type']);
        $this->assertTrue($array['sortable']);
        $this->assertTrue($array['searchable']);
        $this->assertTrue($array['copyable']);
        $this->assertEquals('Mr. ', $array['prefix']);
        $this->assertEquals(' Jr.', $array['suffix']);
    }
}