<?php

declare(strict_types=1);

namespace Vigihdev\Command\DTOs\Projects;

use Vigihdev\Command\Contracts\Projects\ProjectInterface;

/**
 * Class ProjectDto - DTO untuk menyimpan informasi proyek
 */
final class ProjectDto implements ProjectInterface
{

    /**
     * Membuat instance ProjectDto baru
     *
     * @param string $name Nama proyek
     * @param string $rootPath Path direktori root proyek
     */
    public function __construct(
        private readonly string $name,
        private readonly string $rootPath
    ) {}

    /**
     * Mendapatkan nama proyek
     *
     * @return string Nama proyek
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Mendapatkan path root proyek
     *
     * @return string Path direktori root proyek
     */
    public function getRootPath(): string
    {
        return $this->rootPath;
    }
}
