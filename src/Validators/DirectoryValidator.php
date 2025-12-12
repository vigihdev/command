<?php

declare(strict_types=1);

namespace Vigihdev\Command\Validators;

use Vigihdev\Command\Exceptions\DirectoryException;

final class DirectoryValidator
{
    private string $path;

    private function __construct(string $path)
    {
        $this->path = $path;
    }

    public static function validate(string $path): self
    {
        return new self($path);
    }

    public function mustExist(): self
    {
        if (!is_dir($this->path)) {
            throw DirectoryException::notFound($this->path);
        }
        return $this;
    }

    public function mustBeWritable(): self
    {
        if (!is_writable($this->path)) {
            throw DirectoryException::notWritable($this->path);
        }
        return $this;
    }

    public function mustBeReadable(): self
    {
        if (!is_readable($this->path)) {
            throw DirectoryException::notReadable($this->path);
        }
        return $this;
    }

    public function mustBeEmpty(): self
    {
        if (!$this->mustExist()) {
            return $this;
        }

        $files = array_diff(scandir($this->path), ['.', '..']);
        if (!empty($files)) {
            throw DirectoryException::notEmpty($this->path);
        }
        return $this;
    }

    public function mustNotExist(): self
    {
        if (is_dir($this->path)) {
            throw DirectoryException::alreadyExists($this->path);
        }
        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
