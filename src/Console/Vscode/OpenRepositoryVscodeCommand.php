<?php

declare(strict_types=1);

namespace Vigihdev\Command\Console\Vscode;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument, InputOption, InputInterface};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Process\Process;
use Vigihdev\Command\DTOs\Repository\RepositoryInfoDto;

#[AsCommand(
    name: 'vscode:open-repo',
    description: '📦 Vscode Open Directory repositories'
)]
final class OpenRepositoryVscodeCommand extends AbstractVscodeCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'repository',
                InputArgument::REQUIRED,
                'Push specific repository by name',
                null,
                $this->getRepositoryListAutocomplete()
            )
            ->setHelp(
                <<<'HELP'
                    HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $io = new SymfonyStyle($input, $output);
        $repositoryName = $input->getArgument('repository');
        $rootPath = null;

        foreach ($this->repositoryList() as $repo) {
            if ($repo instanceof RepositoryInfoDto && $repo->getRepository()->getName() === $repositoryName) {
                $rootPath = $repo->getRootPath();
            }
        }

        if (!$rootPath) {
            $availables = array_map(fn($dto) => $dto->getRepository()->getName(), $this->repositoryList());
            $available = implode(', ', $availables);
            $io->error("Repository '$repositoryName' not found. Available: $available");
            return Command::FAILURE;
        }

        $homePath = getenv('HOME_PATH') ?: ($_SERVER['HOME'] ?? '');
        if (!$homePath) {
            $io->error('HOME_PATH environment variable not set');
            return Command::FAILURE;
        }


        $basepath = Path::join($homePath, $rootPath);
        if (!is_dir($basepath)) {
            $io->error("Repository directory not found: $basepath");
            return Command::FAILURE;
        }

        try {
            $cmd = sprintf(
                "open -n -b \"com.microsoft.VSCode\" --args %s",
                $basepath
            );
            $process = Process::fromShellCommandline($cmd);
            $process->mustRun();

            if ($process->getOutput()) {
                $io->writeln("<fg=green>{$process->getOutput()}</>");
            }

            if ($process->getErrorOutput()) {
                $io->warning($process->getErrorOutput());
            }

            $io->success("Open {$basepath}");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error("Build failed: " . $e->getMessage());
        }
        return Command::SUCCESS;
    }
}
