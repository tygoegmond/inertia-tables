<?php

namespace Egmond\InertiaTables\Exceptions;

use Exception;

class SerializationException extends Exception
{
    public static function failedToSerialize(string $type, string $reason): self
    {
        return new self("Failed to serialize {$type}: {$reason}");
    }

    public static function invalidData(string $reason): self
    {
        return new self("Invalid data provided for serialization: {$reason}");
    }

    public static function typeScriptGenerationFailed(string $reason): self
    {
        return new self("Failed to generate TypeScript types: {$reason}");
    }
}
