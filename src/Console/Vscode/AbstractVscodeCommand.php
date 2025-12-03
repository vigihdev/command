<?php

declare(strict_types=1);

namespace Vigihdev\Command\Console\Vscode;

use RuntimeException;
use Serializer\Factory\JsonTransformerFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Path;
use Vigihdev\Command\Contracts\Projects\ProjectInterface;
use Vigihdev\Command\DTOs\Projects\ProjectDto;
use Vigihdev\Command\DTOs\Repository\RepositoryInfoDto;

abstract class AbstractVscodeCommand extends Command
{

    private const AVAILABLE_PROJECT_NAMES = [
        'project',
        'npm-project',
        'wp-cli-project'
    ];


    /**
     *
     * @return ProjectDto[]
     */
    protected function getProjectName(string $name): array
    {

        $results = [];
        $filepath = Path::join(getenv('ABSPATH'), getenv('VSCODE_RESOURCE'), "{$name}.json");
        if (!file_exists($filepath)) {
            throw new \RuntimeException("Project file not found: $filepath");
        }

        $projects = file_get_contents($filepath);
        $dataProjects = json_decode($projects, true);
        if (is_array($dataProjects) && !empty($dataProjects)) {
            foreach ($dataProjects as $name => $rootPath) {
                if (is_string($name) && is_string($rootPath)) {
                    $results[] = new ProjectDto(
                        name: $name,
                        rootPath: $rootPath,
                    );
                }
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

    /**
     *
     * @return array<string,string>
     */
    protected function getProjectNpmMap(): array
    {

        $filepath = Path::join(getenv('ABSPATH'), getenv('VSCODE_RESOURCE'), "npm-project.json");
        if (!file_exists($filepath)) {
            throw new \RuntimeException("Project file not found: $filepath");
        }

        $packages = file_get_contents($filepath);
        $packages = json_decode($packages, true);
        return is_array($packages) ? $packages : [];
    }


    /**
     *
     * @return RepositoryInfoDto[]
     */
    protected function repositoryList(): array
    {

        $results = [];

        $filepath = Path::join(getenv('ABSPATH'), getenv('REPO_RESOURCE'), 'repository-list-info.json');
        if (!file_exists($filepath)) {
            throw new \RuntimeException("Project file not found: $filepath");
        }

        try {
            $arrayJson = file_get_contents($filepath);
            $factory = JsonTransformerFactory::create(RepositoryInfoDto::class);
            return $factory->transformArrayJson($arrayJson);
        } catch (\Throwable $e) {
            throw new RuntimeException(
                sprintf("Gagal transformer from file : %s %s", "{$filepath}", $e->getMessage())
            );
        }
        return $results;
    }

    protected function getRepositoryListAutocomplete(): callable
    {
        return function () {
            return array_map(
                fn($repo) => $repo instanceof RepositoryInfoDto ? $repo->getRepository()->getName() : '',
                $this->repositoryList()
            );
        };
    }
}
