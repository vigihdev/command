<?php

declare(strict_types=1);

namespace Vigihdev\Command\Contracts\Repository;

interface RepositoryInterface
{
    public function getName(): string;
    public function getUrl(): string;
}
