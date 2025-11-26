<?php

declare(strict_types=1);

namespace Vigihdev\Command\Contracts;

use Vigihdev\Command\Http\SatisClient;

interface SatisClientManagerInterface
{

    public function getAvailableServiceNames(): array;
    public function getClient(string $name): SatisClient;
}
