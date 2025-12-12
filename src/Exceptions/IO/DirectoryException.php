<?php

declare(strict_types=1);

namespace Vigihdev\Command\Exceptions\IO;

use Throwable;

final class DirectoryException extends IOException
{
    private function __construct(
        string $message = 'Directory operation failed.',
        ?string $path = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $path, $code, $previous);
    }


    public static function notFound(string $path): self
    {
        return new self("Directory not found: {$path}", $path);
    }

    public static function notReadable(string $path): self
    {
        return new self("Directory not readable: {$path}", $path);
    }

    public static function notWritable(string $path): self
    {
        return new self("Directory not writable: {$path}", $path);
    }

    public static function alreadyExists(string $path): self
    {
        return new self("Directory already exists: {$path}", $path);
    }

    public static function notEmpty(string $path): self
    {
        return new self("Directory not empty: {$path}", $path);
    }

    public static function createFailed(string $path, string $reason = ''): self
    {
        $message = "Failed to create directory: {$path}";
        if ($reason !== '') {
            $message .= " - {$reason}";
        }
        return new self($message, $path);
    }

    public static function deleteFailed(string $path, string $reason = ''): self
    {
        $message = "Failed to delete directory: {$path}";
        if ($reason !== '') {
            $message .= " - {$reason}";
        }
        return new self($message, $path);
    }

    public static function operationFailed(
        string $operation,
        string $path,
        string $reason = ''
    ): self {
        $message = "Failed to {$operation} directory: {$path}";
        if ($reason !== '') {
            $message .= " - {$reason}";
        }
        return new self($message, $path);
    }
}
