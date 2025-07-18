<?php

namespace Egmond\InertiaTables\Exceptions;

use Exception;

class InvalidColumnException extends Exception
{
    public static function columnNotFound(string $key): self
    {
        return new self("Column '{$key}' not found in table configuration.");
    }

    public static function invalidColumnType(string $type): self
    {
        return new self("Invalid column type '{$type}' provided.");
    }

    public static function columnNotSortable(string $key): self
    {
        return new self("Column '{$key}' is not sortable.");
    }

    public static function columnNotSearchable(string $key): self
    {
        return new self("Column '{$key}' is not searchable.");
    }
}