<?php

declare(strict_types=1);

namespace Vigihdev\Command\Console\Vscode;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Path;

abstract class AbstractVscodeCommand extends Command
{

    /**
     *
     * @return array<string,string>
     */
    protected function getRepositoryMap(): array
    {

        $filepath = Path::join(getenv('ABSPATH'), getenv('GIT_RESOURCE'), "repository.json");
        if (!file_exists($filepath)) {
            throw new \RuntimeException("Repository file not found: $filepath");
        }

        $packages = file_get_contents($filepath);
        $packages = json_decode($packages, true);
        return is_array($packages) ? $packages : [];
    }

    /**
     *
     * @return array<string,string>
     */
    protected function getProjectMap(): array
    {

        $filepath = Path::join(getenv('ABSPATH'), getenv('VSCODE_RESOURCE'), "project.json");
        if (!file_exists($filepath)) {
            throw new \RuntimeException("Project file not found: $filepath");
        }

        $packages = file_get_contents($filepath);
        $packages = json_decode($packages, true);
        return is_array($packages) ? $packages : [];
    }
}
