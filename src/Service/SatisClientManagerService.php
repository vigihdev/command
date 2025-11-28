<?php

declare(strict_types=1);

namespace Vigihdev\Command\Service;

use Vigihdev\Command\Contracts\ClientSatisInterface;
use Vigihdev\Command\Contracts\SatisClientManagerInterface;
use Vigihdev\Command\Http\SatisClient;
use Vigihdev\Encryption\Contracts\EnvironmentEncryptorServiceContract;

final class SatisClientManagerService implements SatisClientManagerInterface
{

    /**
     * Membangun instance baru dari kelas dengan daftar client dan service enkripsi.
     *
     * @param array $clients Daftar client yang akan digunakan
     * @param EnvironmentEncryptorServiceContract $encryptor Service enkripsi untuk mengelola data sensitif
     */
    public function __construct(
        private readonly array $clients,
        private readonly EnvironmentEncryptorServiceContract $encryptor,
    ) {}

    /**
     * Membuat instance SatisClient berdasarkan nama client
     *
     * @param string $name Nama client yang akan dibuat
     * @return SatisClient Instance SatisClient yang telah dikonfigurasi
     */
    public function getClient(string $name): SatisClient
    {
        return new SatisClient(
            $this->getClientService($name),
            $this->encryptor
        );
    }

    /**
     * Mengambil daftar nama layanan yang tersedia
     *
     * @return array Daftar nama layanan dalam bentuk array
     */
    public function getAvailableServiceNames(): array
    {
        return array_keys($this->clients);
    }

    /**
     * Memeriksa apakah layanan client dengan nama tertentu tersedia dan merupakan instance dari ClientSatisInterface
     *
     * @param string $name Nama layanan client yang akan diperiksa
     * @return bool True jika layanan client tersedia dan sesuai interface, false jika tidak
     */
    private function hasClientService(string $name): bool
    {
        return isset($this->clients[$name]) && $this->clients[$name] instanceof ClientSatisInterface;
    }

    /**
     * Mengambil service client berdasarkan nama
     *
     * @param string $name Nama service yang ingin diambil
     * @return ClientSatisInterface Instance dari service client
     * @throws \InvalidArgumentException Jika service dengan nama tersebut tidak tersedia
     */
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
