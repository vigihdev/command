<?php

declare(strict_types=1);

namespace Vigihdev\Command\Service;

use Vigihdev\Command\Contracts\ClientSatisInterface;
use Vigihdev\Command\Contracts\SatisClientManagerInterface;
use Vigihdev\Command\Http\SatisClient;
use Vigihdev\Encryption\Contracts\EnvironmentEncryptorServiceContract;

final class SatisClientManagerService implements SatisClientManagerInterface
{

    public function __construct(
        private readonly array $clients,
        private readonly EnvironmentEncryptorServiceContract $encryptor,
    ) {}

    public function getClient(string $name): SatisClient
    {
        return new SatisClient(
            $this->getClientService($name),
            $this->encryptor
        );
    }

    public function getAvailableServiceNames(): array
    {
        return array_keys($this->clients);
    }

    private function hasClientService(string $name): bool
    {
        return isset($this->clients[$name]) && $this->clients[$name] instanceof ClientSatisInterface;
    }

    private function getClientService(string $name): ClientSatisInterface
    {

        if (! $this->hasClientService($name)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Service "%s" tidak tersedia. Service yang tersedia: %s',
                    $name,
                    implode(', ', $this->getAvailableServiceNames())
                )
            );
        }

        return $this->clients[$name];
    }
}
