<?php

declare(strict_types=1);

namespace Vigihdev\Command\Contracts;

interface AuthSatisInterface
{
    public function getUsername(): string;
    public function getPassword(): string;
}
