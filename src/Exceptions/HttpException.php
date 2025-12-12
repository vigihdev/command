<?php

declare(strict_types=1);

namespace Vigihdev\Command\Exceptions;

final class HttpException extends \RuntimeException
{
    private ?int $statusCode;

    public function __construct(
        string $message,
        int $statusCode = null,
        int $code = 0,
        \Throwable $previous = null
    ) {
        $this->statusCode = $statusCode;
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public static function requestFailed(string $url, string $error): self
    {
        return new self("Request ke '{$url}' gagal: {$error}");
    }

    public static function badResponse(string $url, int $statusCode): self
    {
        return new self("Response tidak valid dari '{$url}' (Status: {$statusCode})", $statusCode);
    }

    public static function timeout(string $url, int $timeout): self
    {
        return new self("Request timeout ke '{$url}' setelah {$timeout} detik");
    }
}
