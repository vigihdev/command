<?php

declare(strict_types=1);

namespace Vigihdev\Command\DTOs\Repository;

use Vigihdev\Command\Contracts\Repository\RepositoryInfoInterface;
use Vigihdev\Command\Contracts\Repository\RepositoryInterface;

/**
 * Class RepositoryInfoDto - Menyimpan informasi dasar tentang repository
 */
final class RepositoryInfoDto implements RepositoryInfoInterface
{


    /**
     *
     * @param RepositoryDto $repository
     * @param string $rootPath
     * @return void
     */
    public function __construct(
        private readonly RepositoryInterface $repository,
        private readonly string $rootPath,
    ) {}


    public function getRepository(): RepositoryInterface
    {
        return $this->repository;
    }

    public function getRootPath(): string
    {
        return $this->rootPath;
    }
}
