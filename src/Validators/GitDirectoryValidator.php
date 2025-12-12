<?php

declare(strict_types=1);

namespace Vigihdev\Command\Validators;

use Vigihdev\Command\Exceptions\GitDirectoryException;

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
}
