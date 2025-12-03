<?php

declare(strict_types=1);

namespace Vigihdev\Command\Contracts\Repository;

interface RepositoryInfoInterface extends PathRepositoryInterface
{
    public function getRepository(): RepositoryInterface;
}
