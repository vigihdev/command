<?php

declare(strict_types=1);

namespace Vigihdev\Command\Exceptions;

final class GitException extends \RuntimeException
{
    public function __construct(
        string $message,
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function directoryGitNotFound($path): self
    {
        return new self("Directory (.git) tidak tersedia di : '{$path}'");
    }

    public static function cloneFailed(string $repoUrl, string $error): self
    {
        return new self("Gagal clone repository '{$repoUrl}': {$error}");
    }

    public static function invalidUrl(string $url): self
    {
        return new self("URL Git tidak valid: '{$url}'. Format yang diterima: HTTPS/SSH");
    }

    public static function repositoryExists(string $path): self
    {
        return new self("Repository sudah ada di lokasi: '{$path}'");
    }

    public static function remoteUnreachable(string $url): self
    {
        return new self("Repository remote tidak dapat diakses: '{$url}'. Periksa koneksi atau URL.");
    }

    public static function authenticationFailed(string $url): self
    {
        return new self("Autentikasi gagal untuk: '{$url}'. Periksa credentials atau SSH key.");
    }
}
