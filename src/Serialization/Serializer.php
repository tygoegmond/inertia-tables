<?php

namespace Egmond\InertiaTables\Serialization;

use Egmond\InertiaTables\Columns\BaseColumn;
use Egmond\InertiaTables\TableResult;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class Serializer
{
    public static function serializeTableResult(TableResult $result): array
    {
        return [
            'config' => static::serializeConfig($result->config),
            'data' => static::serializeData($result->data),
            'pagination' => static::serializePagination($result->pagination),
            'sort' => static::serializeSort($result->sort),
            'search' => $result->search,
        ];
    }

    public static function serializeConfig(array $config): array
    {
        return [
            'columns' => static::serializeColumns($config['columns'] ?? []),
            'searchable' => $config['searchable'] ?? false,
            'perPage' => $config['perPage'] ?? 25,
            'defaultSort' => $config['defaultSort'] ?? [],
        ];
    }

    public static function serializeColumns(array $columns): array
    {
        return array_map(function ($column) {
            if ($column instanceof BaseColumn) {
                return $column->toArray();
            }

            return $column;
        }, $columns);
    }

    public static function serializeData(array $data): array
    {
        return array_map(function ($item) {
            if ($item instanceof Arrayable) {
                return $item->toArray();
            }
            if ($item instanceof Jsonable) {
                return json_decode($item->toJson(), true);
            }

            return $item;
        }, $data);
    }

    public static function serializePagination(array $pagination): array
    {
        return [
            'current_page' => $pagination['current_page'] ?? 1,
            'per_page' => $pagination['per_page'] ?? 25,
            'total' => $pagination['total'] ?? 0,
            'last_page' => $pagination['last_page'] ?? 1,
            'from' => $pagination['from'] ?? null,
            'to' => $pagination['to'] ?? null,
            'links' => static::serializePaginationLinks($pagination['links'] ?? []),
        ];
    }

    public static function serializePaginationLinks(array $links): array
    {
        return array_map(function ($link) {
            return [
                'url' => $link['url'] ?? null,
                'label' => $link['label'] ?? '',
                'active' => $link['active'] ?? false,
            ];
        }, $links);
    }

    public static function serializeSort(array $sort): array
    {
        return $sort;
    }
}
