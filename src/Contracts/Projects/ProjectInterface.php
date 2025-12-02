<?php

declare(strict_types=1);

namespace Vigihdev\Command\Contracts\Projects;

/**
 * Interface ProjectInterface - Kontrak untuk objek proyek yang mendefinisikan properti dasar proyek
 */
interface ProjectInterface
{
    /**
     * Mendapatkan nama proyek
     *
     * @return string Nama proyek
     */
    public function getName(): string;
    
    /**
     * Mendapatkan path root proyek
     *
     * @return string Path direktori root proyek
     */
    public function getRootPath(): string;
}