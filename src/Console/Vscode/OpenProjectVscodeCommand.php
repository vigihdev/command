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

#[AsCommand(
    name: 'vscode:open-project',
    description: '📦 Vscode Open Project Directory'
)]
final class OpenProjectVscodeCommand extends AbstractVscodeCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Project specific by name',
                null,
                $this->getProjectAutocomplete()
            )
            ->setHelp(
                <<<'HELP'
                    HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $io = new SymfonyStyle($input, $output);
        $projectName = $input->getArgument('name');

        $projects = array_filter($this->listProjectNames(), fn($dto) => $dto->getName() === $projectName);
        if (empty($projects)) {
            $availables = array_map(fn($dto) => $dto->getName(), $this->listProjectNames());
            $available = implode(', ', $availables);
            $io->error("Project '$projectName' not found. Available: $available");
            return Command::FAILURE;
        }

        $homePath = getenv('HOME_PATH') ?: ($_SERVER['HOME'] ?? '');
        if (!$homePath) {
            $io->error('HOME_PATH environment variable not set');
            return Command::FAILURE;
        }

        $project = current($projects);
        $basepath = Path::join($homePath, $project->getRootPath());
        if (!is_dir($basepath)) {
            $io->error("Project directory not found: $basepath");
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
