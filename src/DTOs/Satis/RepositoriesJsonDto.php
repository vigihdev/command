<?php

declare(strict_types=1);

namespace Vigihdev\Command\DTOs\Satis;

use Vigihdev\Command\Contracts\Satis\RepositoriesJsonInterface;

final class RepositoriesJsonDto implements RepositoriesJsonInterface
{

    public function __construct(
        private readonly string $type,
        private readonly string $url,
    ) {}

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
