<?php

declare(strict_types=1);

namespace Vigihdev\Command\Console\Terminal;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Path;
use Vigihdev\Command\Contracts\Projects\ProjectInterface;
use Vigihdev\Command\DTOs\Projects\ProjectDto;

abstract class AbstractTerminalCommand extends Command
{
    private const AVAILABLE_PROJECT_NAMES = [
        'host-project',
        'npm-repository-project',
        'composer-repository-project',
        'wp-cli-repository-project'
    ];

    /**
     *
     * @return ProjectDto[]
     */
    protected function getProjectName(string $name): array
    {

        $results = [];
        $filepath = Path::join(getenv('ABSPATH'), getenv('PROJECTS_RESOURCE'), "{$name}.json");
        if (!file_exists($filepath)) {
            throw new \RuntimeException("Project file not found: $filepath");
        }

        $packages = file_get_contents($filepath);
        $packages = json_decode($packages, true);
        if (is_array($packages) && !empty($packages)) {
            foreach ($packages as $package) {
                $results[] = new ProjectDto(
                    name: $package['name'] ?? '',
                    rootPath: $package['rootPath'] ?? '',
                );
            }
        }
        return $results;
    }

    protected function getProjectAutocomplete(): callable
    {
        return function () {
            return array_map(
                fn($project) => $project instanceof ProjectInterface ? $project->getName() : '',
                $this->listProjectNames()
            );
        };
    }

    /**
     *
     * @return ProjectDto[]
     */
    protected function listProjectNames(): array
    {
        $projects = [];
        foreach (self::AVAILABLE_PROJECT_NAMES as $name) {
            array_push($projects, ...$this->getProjectName($name));
        }
        return $projects;
    }
}
