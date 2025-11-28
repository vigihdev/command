<?php

declare(strict_types=1);

namespace Vigihdev\Command\Contracts\Satis;

interface RepositoriesJsonInterface
{
    /**
     * Mendapatkan tipe repositori
     *
     * @return string Tipe repositori
     */
    public function getType(): string;

    /**
     * Mendapatkan URL repositori
     *
     * @return string URL dari repositori
     */
    public function getUrl(): string;
}
