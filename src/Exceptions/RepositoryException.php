<?php

declare(strict_types=1);

namespace Vigihdev\Command\Exceptions;


final class RepositoryException extends \InvalidArgumentException
{
    public static function invalidLocalPath(string $path): self
    {
        return new self("Path lokal tidak valid: '{$path}'. Path harus berupa direktori.");
    }

    public static function directoryNotEmpty(string $path): self
    {
        return new self("Direktori tidak kosong: '{$path}'. Gunakan --force untuk overwrite.");
    }

    public static function notWritable(string $path): self
    {
        return new self("Direktori tidak dapat ditulisi: '{$path}'. Periksa permissions.");
    }

    public static function nameAlreadyExists(string $name): self
    {
        return new self("Nama proyek '{$name}' sudah digunakan. Pilih nama lain.");
    }
}
