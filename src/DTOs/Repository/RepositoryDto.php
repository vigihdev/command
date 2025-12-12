<?php

declare(strict_types=1);

namespace Vigihdev\Command\DTOs\Repository;

use Vigihdev\Command\Contracts\Repository\RepositoryInterface;
use Vigihdev\Command\DTOs\BaseDto;

final class RepositoryDto extends BaseDto implements RepositoryInterface
{

    public function __construct(
        private readonly string $url,
        private readonly string $name,
    ) {}

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
