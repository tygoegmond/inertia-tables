<?php

namespace Egmond\InertiaTables\Serialization;

use Egmond\InertiaTables\Columns\BaseColumn;
use Egmond\InertiaTables\Filters\BaseFilter;

class ConfigurationSerializer
{
    public static function serializeColumn(BaseColumn $column): array
    {
        return [
            'key' => $column->getKey(),
            'label' => $column->getLabel(),
            'type' => $column->getType(),
            'visible' => $column->isVisible(),
            'sortable' => $column->isSortable(),
            'searchable' => $column->isSearchable(),
            'searchColumn' => $column->getSearchColumn(),
            'defaultSort' => $column->getDefaultSortDirection(),
            'config' => $column->getState(),
        ];
    }

    public static function serializeFilter(BaseFilter $filter): array
    {
        return [
            'key' => $filter->getKey(),
            'label' => $filter->getLabel(),
            'type' => $filter->getType(),
            'visible' => $filter->isVisible(),
            'defaultValue' => $filter->getDefaultValue(),
            'config' => $filter->getState(),
        ];
    }

    public static function generateTypeScriptTypes(array $columns, array $filters): string
    {
        $columnTypes = static::generateColumnTypes($columns);
        $filterTypes = static::generateFilterTypes($filters);

        return "// Auto-generated TypeScript types\n\n" .
            "export interface TableConfig {\n" .
            "  columns: ColumnConfig[];\n" .
            "  filters: FilterConfig[];\n" .
            "  searchable: boolean;\n" .
            "  perPage: number;\n" .
            "  defaultSort: Record<string, 'asc' | 'desc'>;\n" .
            "}\n\n" .
            $columnTypes . "\n\n" .
            $filterTypes;
    }

    protected static function generateColumnTypes(array $columns): string
    {
        $types = "export type ColumnConfig = ";
        $columnTypes = [];

        foreach ($columns as $column) {
            $columnTypes[] = static::getColumnTypeInterface($column);
        }

        return $types . implode(' | ', $columnTypes) . ';';
    }

    protected static function generateFilterTypes(array $filters): string
    {
        $types = "export type FilterConfig = ";
        $filterTypes = [];

        foreach ($filters as $filter) {
            $filterTypes[] = static::getFilterTypeInterface($filter);
        }

        return $types . implode(' | ', $filterTypes) . ';';
    }

    protected static function getColumnTypeInterface(BaseColumn $column): string
    {
        $type = $column->getType();
        $interfaceName = ucfirst($type) . 'ColumnConfig';

        return $interfaceName;
    }

    protected static function getFilterTypeInterface(BaseFilter $filter): string
    {
        $type = $filter->getType();
        $interfaceName = ucfirst(str_replace('_', '', $type)) . 'FilterConfig';

        return $interfaceName;
    }
}