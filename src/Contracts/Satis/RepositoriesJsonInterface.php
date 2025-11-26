<?php

declare(strict_types=1);

namespace Vigihdev\Command\Contracts\Satis;

interface RepositoriesJsonInterface
{
    public function getType(): string;
    public function getUrl(): string;
}
