<?php

declare(strict_types=1);

namespace Vigihdev\Command\DTOs\Satis;

use Vigihdev\Command\Contracts\Satis\RepositoriesJsonInterface;

final class RepositoriesJsonDto implements RepositoriesJsonInterface
{

    /**
     * 
     * @param string $type
     * @param string $url
     */
    public function __construct(
        private readonly string $type,
        private readonly string $url,
    ) {}


    /**
     * Mengambil nilai URL dari objek saat ini.
     *
     * @return string Nilai URL yang tersimpan dalam properti url
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     *  Get the value of type
     *  
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
