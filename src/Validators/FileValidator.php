<?php

declare(strict_types=1);

namespace Vigihdev\Command\Validators;

use Vigihdev\Command\Exceptions\FileException;

final class FileValidator
{
    private string $filepath;
    private mixed $content = null;

    private function __construct(string $filepath)
    {
        $this->filepath = $filepath;
    }

    public static function validate(string $filepath): self
    {
        return new self($filepath);
    }

    public function mustExist(): self
    {
        if (!file_exists($this->filepath)) {
            throw FileException::notFound($this->filepath);
        }
        return $this;
    }

    public function mustBeReadable(): self
    {
        if (!is_readable($this->filepath)) {
            throw FileException::notReadable($this->filepath);
        }
        return $this;
    }

    public function mustBeWritable(): self
    {
        if (!is_writable($this->filepath)) {
            throw FileException::notWritable($this->filepath);
        }
        return $this;
    }

    public function mustBeFile(): self
    {
        if (!is_file($this->filepath)) {
            throw FileException::invalidFormat(
                $this->filepath,
                'Path is not a file'
            );
        }
        return $this;
    }

    public function mustBeJson(): self
    {
        $this->mustExist()->mustBeReadable();

        $content = file_get_contents($this->filepath);
        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw FileException::invalidFormat(
                $this->filepath,
                'Invalid JSON: ' . json_last_error_msg()
            );
        }

        $this->content = $decoded;
        return $this;
    }

    public function mustNotExist(): self
    {
        if (file_exists($this->filepath)) {
            throw FileException::alreadyExists($this->filepath);
        }
        return $this;
    }

    public function getContent(): mixed
    {
        return $this->content;
    }

    public function getFilepath(): string
    {
        return $this->filepath;
    }
}
