<?php

declare(strict_types=1);

namespace Vigihdev\Command\Exceptions;

use RuntimeException;
use Throwable;

final class UrlException extends RuntimeException
{
    // Daftar kode error biar gampang di-handle
    public const EMPTY             = 'EMPTY_URL';
    public const INVALID           = 'INVALID_URL';
    public const MALFORMED         = 'MALFORMED_URL';
    public const UNSUPPORTED_PROTOCOL = 'UNSUPPORTED_PROTOCOL';
    public const GIT_NOT_SUPPORTED = 'GIT_URL_NOT_SUPPORTED';
    public const UNSAFE_CREDENTIAL = 'UNSAFE_URL_CREDENTIAL';
    public const LOCAL_NOT_ALLOWED = 'LOCAL_URL_NOT_ALLOWED';
    public const TOO_LONG          = 'URL_TOO_LONG';

    public function __construct(
        string $message,
        public readonly string $errorCode,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    // === Factory methods — ini yang bikin enak dipakai! ===

    public static function empty(): self
    {
        return new self('URL tidak boleh kosong atau null.', self::EMPTY);
    }

    public static function invalid(string $url): self
    {
        return new self("URL tidak valid: {$url}", self::INVALID);
    }

    public static function malformed(string $url, string $reason = ''): self
    {
        $msg = "URL malformed: {$url}";
        if ($reason) $msg .= " → {$reason}";
        return new self($msg, self::MALFORMED);
    }

    public static function unsupportedProtocol(string $protocol): self
    {
        return new self(
            "Protokol '{$protocol}' tidak didukung. Gunakan http, https, ssh, atau git.",
            self::UNSUPPORTED_PROTOCOL
        );
    }

    public static function gitNotSupported(string $url): self
    {
        return new self(
            "Format Git URL tidak didukung: {$url}\nContoh benar:\n• https://github.com/user/repo.git\n• git@github.com:user/repo.git",
            self::GIT_NOT_SUPPORTED
        );
    }

    public static function unsafeCredential(string $url): self
    {
        return new self(
            "URL mengandung username/password (bahaya!): {$url}\nHindari menyimpan credential di URL.",
            self::UNSAFE_CREDENTIAL
        );
    }

    public static function localNotAllowed(string $url): self
    {
        return new self("URL lokal/localhost tidak diperbolehkan: {$url}", self::LOCAL_NOT_ALLOWED);
    }

    public static function tooLong(string $url, int $max = 2048): self
    {
        return new self("URL terlalu panjang ({$max} karakter max): " . strlen($url) . " karakter", self::TOO_LONG);
    }
}
