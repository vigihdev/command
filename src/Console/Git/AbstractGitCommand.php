<?php

declare(strict_types=1);

namespace Vigihdev\Command\Console\Git;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Path;

abstract class AbstractGitCommand extends Command
{


    /**
     * 
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
     * Get packages from json file
     * 
     * @param string $filename
     * @return array<string,string>
     */
    protected function getPackages(string $filename): array
    {

        $filepath = Path::join(getenv('ABSPATH'), getenv('GIT_RESOURCE'), "{$filename}.json");
        if (!file_exists($filepath)) {
            throw new \RuntimeException("Packages file not found: $filepath");
        }

        $packages = file_get_contents($filepath);
        $packages = json_decode($packages, true);
        return is_array($packages) ? $packages : [];
    }
}
