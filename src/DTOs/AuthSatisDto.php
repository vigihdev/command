<?php

declare(strict_types=1);

namespace Vigihdev\Command\DTOs;

use Vigihdev\Command\Contracts\AuthSatisInterface;

final class AuthSatisDto implements AuthSatisInterface
{
    public function __construct(
        private readonly string $username,
        private readonly string $password
    ) {}


    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
