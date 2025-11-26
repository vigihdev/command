<?php

declare(strict_types=1);

namespace Vigihdev\Command\DTOs;

use Vigihdev\Command\Contracts\AuthSatisInterface;
use Vigihdev\Command\Contracts\ClientSatisInterface;

final class ClientSatisDto implements ClientSatisInterface
{

    /**
     * @param string $baseUri
     * @param int $timeout
     * @param AuthSatisDto $auth
     */
    public function __construct(
        private readonly string $baseUri,
        private readonly AuthSatisInterface $auth,
        private readonly int $timeout = 120,
    ) {}

    public function getAuth(): AuthSatisInterface
    {
        return $this->auth;
    }

    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }
}
