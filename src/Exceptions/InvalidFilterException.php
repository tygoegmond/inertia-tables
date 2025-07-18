<?php

namespace Egmond\InertiaTables\Exceptions;

use Exception;

class InvalidFilterException extends Exception
{
    public static function filterNotFound(string $key): self
    {
        return new self("Filter '{$key}' not found in table configuration.");
    }

    public static function invalidFilterType(string $type): self
    {
        return new self("Invalid filter type '{$type}' provided.");
    }

    public static function invalidFilterValue(string $key, mixed $value): self
    {
        $valueType = gettype($value);
        return new self("Invalid value of type '{$valueType}' provided for filter '{$key}'.");
    }
}