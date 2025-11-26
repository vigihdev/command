<?php

declare(strict_types=1);

namespace Vigihdev\Command\Contracts\Satis;

interface SatisJsonInterface
{

    public function getName(): string;
    public function getHomepage(): string;

    /**
     *
     * @return RepositoriesJsonInterface[]
     */
    public function getRepositories(): array;
}
