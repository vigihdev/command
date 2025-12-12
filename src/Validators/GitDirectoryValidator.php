<?php

declare(strict_types=1);

namespace Vigihdev\Command\Validators;

use Vigihdev\Command\Exceptions\GitDirectoryException;
use Vigihdev\Command\Exceptions\GitException;

final class GitDirectoryValidator
{

    public function __construct(
        private readonly string $path
    ) {}

    public static function validate(string $path): self
    {
        return new self($path);
    }

    public function mustBeInitialized(): self
    {
        if (!is_dir($this->path . '/.git')) {
            throw GitDirectoryException::notInitialized($this->path);
        }
        return $this;
    }

    public function mustBeValidGitUrl(string $url)
    {
        if (! $this->isValidateUrlGit($url)) {
            throw GitException::invalidUrl($url);
        }
    }

    private function isValidateUrlGit(string $url): bool
    {
        $param = parse_url($url);
        return substr($param['path'] ?? '', -4, 4) === '.git';
    }
}
