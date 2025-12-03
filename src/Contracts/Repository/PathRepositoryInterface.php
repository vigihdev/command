<?php

declare(strict_types=1);

namespace Vigihdev\Command\Contracts\Repository;

interface PathRepositoryInterface
{
    public function getRootPath(): string;
}
