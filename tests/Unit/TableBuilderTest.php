<?php

namespace Egmond\InertiaTables\Tests\Unit;

use Egmond\InertiaTables\Builder\TableBuilder;
use Egmond\InertiaTables\Columns\TextColumn;
use Egmond\InertiaTables\Columns\BadgeColumn;
use Egmond\InertiaTables\Filters\SelectFilter;
use Egmond\InertiaTables\Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TableBuilderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_table_builder(): void
    {
        $builder = TableBuilder::make();

        $this->assertInstanceOf(TableBuilder::class, $builder);
    }

    /** @test */
    public function it_can_add_columns(): void
    {
        $builder = TableBuilder::make()
            ->columns([
                TextColumn::make('name')->sortable(),
                BadgeColumn::make('status'),
            ]);

        $this->assertInstanceOf(TableBuilder::class, $builder);
    }

    /** @test */
    public function it_can_add_filters(): void
    {
        $builder = TableBuilder::make()
            ->filters([
                SelectFilter::make('status')->options(['active', 'inactive']),
            ]);

        $this->assertInstanceOf(TableBuilder::class, $builder);
    }

    /** @test */
    public function it_can_set_pagination(): void
    {
        $builder = TableBuilder::make()
            ->paginate(50);

        $this->assertInstanceOf(TableBuilder::class, $builder);
    }

    /** @test */
    public function it_can_set_searchable(): void
    {
        $builder = TableBuilder::make()
            ->searchable();

        $this->assertInstanceOf(TableBuilder::class, $builder);
    }

    /** @test */
    public function it_can_set_default_sort(): void
    {
        $builder = TableBuilder::make()
            ->sortBy('name', 'desc');

        $this->assertInstanceOf(TableBuilder::class, $builder);
    }
}