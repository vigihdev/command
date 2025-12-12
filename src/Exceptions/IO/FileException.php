<?php

declare(strict_types=1);

namespace Vigihdev\Command\Exceptions\IO;

use Throwable;

final class FileException extends IOException
{
    private function __construct(
        string $message = 'File operation failed.',
        ?string $path = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $path, $code, $previous);
    }

    public static function notFound(string $path): self
    {
        return new self("File not found: {$path}", $path);
    }

    public static function notReadable(string $path): self
    {
        return new self("File not readable: {$path}", $path);
    }

    public static function notWritable(string $path): self
    {
        return new self("File not writable: {$path}", $path);
    }

    public static function alreadyExists(string $path): self
    {
        return new self("File already exists: {$path}", $path);
    }

    public static function invalidFormat(string $path, string $reason = ''): self
    {
        $message = "Invalid file format: {$path}";
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
        $message = "Failed to {$operation} file: {$path}";
        if ($reason !== '') {
            $message .= " - {$reason}";
        }
        return new self($message, $path);
    }
}
