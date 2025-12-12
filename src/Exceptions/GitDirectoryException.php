<?php

declare(strict_types=1);

namespace Vigihdev\Command\Exceptions;

use Throwable;

/**
 * Class GitDirectoryException
 * 
 * Exception khusus yang dilempar ketika terjadi kesalahan status
 * atau operasi pada repositori Git di suatu direktori.
 * 
 */
final class GitDirectoryException extends \RuntimeException
{
    private function __construct(
        string $message = 'Git Directory operation failed.',
        ?string $path = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }


    /**
     * Membuat instance GitDirectoryException untuk kasus direktori belum diinisialisasi Git.
     */
    public static function notInitialized(string $path): self
    {
        return new self("Not a valid Git repository: {$path} (Missing .git folder)", $path);
    }

    /**
     * Membuat instance GitDirectoryException untuk kasus repositori sedang 'dirty' (ada perubahan).
     */
    public static function isDirty(string $path): self
    {
        return new self("Git repository has uncommitted changes (Dirty status): {$path}", $path);
    }

    /**
     * Membuat instance GitDirectoryException untuk kasus repositori adalah bare repository.
     */
    public static function isBare(string $path): self
    {
        return new self("Git repository is a bare repository and cannot be used as a working copy: {$path}", $path);
    }

    /**
     * Membuat instance GitDirectoryException untuk kasus remote tertentu tidak ditemukan.
     */
    public static function remoteNotFound(string $path, string $remoteName): self
    {
        return new self("Git remote '{$remoteName}' not found in repository: {$path}", $path);
    }

    /**
     * Membuat instance GitDirectoryException untuk error umum atau tidak terduga dari perintah Git.
     */
    public static function unknownError(string $path, string $commandOutput): self
    {
        return new self("Unknown Git error encountered in {$path}. Output: {$commandOutput}", $path);
    }
}
