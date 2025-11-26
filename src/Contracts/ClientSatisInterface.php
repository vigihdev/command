<?php

declare(strict_types=1);

namespace Vigihdev\Command\Contracts;

interface ClientSatisInterface
{

    public function getBaseUri(): string;
    public function getTimeout(): int;
    public function getAuth(): AuthSatisInterface;
}
