<?php

declare(strict_types=1);

namespace Vigihdev\Command\Validators;

use Vigihdev\Command\Exceptions\UrlException;

final class UrlValidator
{
    private const MAX_URL_LENGTH = 2048;

    private function __construct() {} // biar gak bisa di-instantiate

    /**
     * Validasi URL Git Repository URL
     * Lempar UrlException kalau gak lolos
     */
    public static function gitRepository(string $url): void
    {
        self::notEmpty($url);
        self::notTooLong($url);
        self::noCredentialInUrl($url);
        // self::noLocalUrlInProduction($url);

        // Cek format standar Git
        $validPatterns = [
            // HTTPS / HTTP
            '#^https?://(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\\b([-a-zA-Z0-9()@:%_\+.~#?&//=]*)\.git$#i',
            // SSH biasa (git@github.com:user/repo.git)
            '#^git@[-a-zA-Z0-9.]+:[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+\.git$#',
            // SSH dengan port atau protokol eksplisit
            '#^ssh://git@[-a-zA-Z0-9.]+(:\d+)?/[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+\.git$#',
            // Git protocol (jarang tapi masih ada)
            '#^git://[-a-zA-Z0-9.]+/[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+\.git$#',
        ];

        foreach ($validPatterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return; // LOLOS!
            }
        }

        throw UrlException::gitNotSupported($url);
    }

    // Kalau kamu cuma butuh validasi URL biasa (bukan khusus Git)
    public static function any(string $url): void
    {
        self::notEmpty($url);
        self::notTooLong($url);
        self::noCredentialInUrl($url);

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw UrlException::invalid($url);
        }
    }

    private static function notEmpty(string $url): void
    {
        if (trim($url) === '') {
            throw UrlException::empty();
        }
    }

    private static function notTooLong(string $url): void
    {
        if (strlen($url) > self::MAX_URL_LENGTH) {
            throw UrlException::tooLong($url, self::MAX_URL_LENGTH);
        }
    }

    private static function noCredentialInUrl(string $url): void
    {
        // Deteksi user:pass@ di dalam URL
        if (preg_match('#://[^:@]+:[^:@]+@#', $url)) {
            throw UrlException::unsafeCredential($url);
        }
    }
}
