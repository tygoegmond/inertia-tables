<?php

namespace Egmond\InertiaTables\Serialization;

use Egmond\InertiaTables\Columns\BaseColumn;

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


    public static function generateTypeScriptTypes(array $columns): string
    {
        $columnTypes = static::generateColumnTypes($columns);

        return "// Auto-generated TypeScript types\n\n".
            "export interface TableConfig {\n".
            "  columns: ColumnConfig[];\n".
            "  searchable: boolean;\n".
            "  perPage: number;\n".
            "  defaultSort: Record<string, 'asc' | 'desc'>;\n".
            "}\n\n".
            $columnTypes;
    }

    protected static function generateColumnTypes(array $columns): string
    {
        $types = 'export type ColumnConfig = ';
        $columnTypes = [];

        foreach ($columns as $column) {
            $columnTypes[] = static::getColumnTypeInterface($column);
        }

        return $types.implode(' | ', $columnTypes).';';
    }


    protected static function getColumnTypeInterface(BaseColumn $column): string
    {
        $type = $column->getType();
        $interfaceName = ucfirst($type).'ColumnConfig';

        return $interfaceName;
    }

}
