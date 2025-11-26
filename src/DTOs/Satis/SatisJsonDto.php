<?php

declare(strict_types=1);

namespace Vigihdev\Command\DTOs\Satis;

use Vigihdev\Command\Contracts\Satis\SatisJsonInterface;

final class SatisJsonDto implements SatisJsonInterface
{
    /**
     * @param RepositoriesJsonDto[] $repositories
     */
    public function __construct(
        private readonly string $homepage,
        private readonly string $name,
        private readonly array $repositories,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getHomepage(): string
    {
        return $this->homepage;
    }

    /**
     *
     * @return RepositoriesJsonDto[]
     */
    public function getRepositories(): array
    {
        return $this->repositories;
    }
}
