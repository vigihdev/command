<?php

declare(strict_types=1);

namespace Vigihdev\Command\Validators;

use Vigihdev\Command\Exceptions\GitException;

final class GitValidator
{

    public function __construct(
        private readonly string $path
    ) {}

    public static function validate(string $path): self
    {
        return new self($path);
    }

    public function mustBeValidUrl(): self
    {
        if (! $this->isValidGitUrl($this->path)) {
            throw GitException::invalidUrl($this->path);
        }
        return $this;
    }

    private function isValidGitUrl(string $url): void
    {
        // Bersihkan URL
        $cleanUrl = trim($url);

        // Cek format dasar
        if (empty($cleanUrl)) {
            throw new GitException('URL repository tidak boleh kosong');
        }

        // Pattern untuk URL Git yang valid
        $patterns = [
            // GitHub
            '/^https:\/\/(www\.)?github\.com\/[a-zA-Z0-9_.-]+\/[a-zA-Z0-9_.-]+(\.git)?$/i',
            '/^git@github\.com:[a-zA-Z0-9_.-]+\/[a-zA-Z0-9_.-]+(\.git)?$/i',

            // GitLab
            '/^https:\/\/(www\.)?gitlab\.com\/[a-zA-Z0-9_.-]+\/[a-zA-Z0-9_.-]+(\.git)?$/i',
            '/^git@gitlab\.com:[a-zA-Z0-9_.-]+\/[a-zA-Z0-9_.-]+(\.git)?$/i',

            // Bitbucket
            '/^https:\/\/([a-zA-Z0-9_.-]+@)?bitbucket\.org\/[a-zA-Z0-9_.-]+\/[a-zA-Z0-9_.-]+(\.git)?$/i',
            '/^git@bitbucket\.org:[a-zA-Z0-9_.-]+\/[a-zA-Z0-9_.-]+(\.git)?$/i',

            // Generic SSH/HTTP
            '/^(https?|git|ssh):\/\/.+/i',
            '/^[a-zA-Z0-9_.-]+@[a-zA-Z0-9_.-]+:[a-zA-Z0-9_.-\/]+(\.git)?$/',
        ];

        $isValid = false;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $cleanUrl)) {
                $isValid = true;
                break;
            }
        }

        if (!$isValid) {
            $normalizedUrl = $this->normalizeGitUrl($cleanUrl);

            if ($normalizedUrl !== $cleanUrl) {
                throw GitException::invalidUrl($url);
            }
            // throw GitException::invalidUrl($cleanUrl);
        }
    }

    private function normalizeGitUrl(string $url): string
    {
        // Fix common typos
        $fixes = [
            '/\.gitr$/' => '.git',
            '/\.gitt$/' => '.git',
            '/\.git\.git$/' => '.git',
            '/github\.com\/(.+)\/$/' => 'github.com/$1',
        ];

        $normalized = $url;
        foreach ($fixes as $pattern => $replacement) {
            $normalized = preg_replace($pattern, $replacement, $normalized);
        }

        return $normalized;
    }
}
