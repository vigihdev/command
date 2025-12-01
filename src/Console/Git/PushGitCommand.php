<?php

declare(strict_types=1);

namespace Vigihdev\Command\Console\Git;

use DateTime;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument, InputOption, InputInterface};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'git:push',
    description: 'Push Git packages'
)]
final class PushGitCommand extends AbstractGitCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'cwd',
                null,
                InputOption::VALUE_OPTIONAL,
                'Push specific repository by name',
                null,
                function () {
                    return array_merge(
                        array_keys($this->getRepositoryMap()),
                        array_keys($this->getRepositoryNpmMap())
                    );
                }
            )
            ->setHelp(
                <<<'HELP'
                    The <info>git:push</info> command automates git push workflow:

                    <info>console git:push</info>          Push current directory
                    <info>console git:push --cwd=contracts</info>  Push specific repository

                    Available repositories:
                    • contracts
                    • dto
                    • utils
                    • wp-daftar-harga

                    HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $cwd = $input->getOption('cwd');

        if ($cwd) {
            return $this->processSpecificRepository($io, $cwd);
        }

        return $this->processCurrentDirectory($io);
    }

    private function processSpecificRepository(SymfonyStyle $io, string $repositoryName): int
    {
        $cwdList = array_merge(
            $this->getRepositoryMap(),
            $this->getRepositoryNpmMap(),
        );

        if (!isset($cwdList[$repositoryName])) {
            $available = implode(', ', array_keys($cwdList));
            $io->error("Repository '$repositoryName' not found. Available: $available");
            return Command::FAILURE;
        }

        $homePath = getenv('HOME_PATH') ?: ($_SERVER['HOME'] ?? '');
        if (!$homePath) {
            $io->error('HOME_PATH environment variable not set');
            return Command::FAILURE;
        }

        $basepath = Path::join($homePath, $cwdList[$repositoryName]);
        if (!is_dir($basepath)) {
            $io->error("Repository directory not found: $basepath");
            return Command::FAILURE;
        }

        $io->note("Pushing repository: $repositoryName On Path {$basepath}");
        return $this->processPush($io, $basepath, $repositoryName);
    }

    private function processCurrentDirectory(SymfonyStyle $io): int
    {
        $currentDir = getcwd();
        $dirName = basename($currentDir);

        // Check if current directory is a git repository
        if (!is_dir($currentDir . '/.git')) {
            $io->error("Current directory is not a git repository: $currentDir");
            return Command::FAILURE;
        }

        $io->note("Pushing current directory: $dirName");
        return $this->processPush($io, $currentDir, $dirName);
    }

    private function processPush(SymfonyStyle $io, string $cwd, string $repositoryName): int
    {
        $io->section("Push Repository: $repositoryName");

        $originalDir = getcwd();
        $changedDir = false;

        if ($cwd !== $originalDir) {
            chdir($cwd);
            $changedDir = true;
        }

        try {
            // Validate git repository
            $validateProcess = new Process(['git', 'status']);
            $validateProcess->run();

            if (!$validateProcess->isSuccessful()) {
                throw new \RuntimeException('Not a valid git repository or git command failed');
            }

            // Step 1: Add changes
            $process = new Process(['git', 'add', '.']);
            $process->mustRun();
            $io->writeln("<fg=green>✓ Changes staged</>");

            // Step 2: Check if there are changes to commit
            $checkProcess = new Process(['git', 'diff-index', '--quiet', 'HEAD', '--']);
            $hasChanges = $checkProcess->run() !== 0;

            if ($hasChanges) {
                // Step 3: Commit if changes exist
                $date = (new DateTime())->format('Y-m-d H:i:s');
                $commitProcess = new Process(['git', 'commit', '-m', "💥 {$date}"]);
                $commitProcess->mustRun();
                $io->writeln("<fg=green>✓ Changes committed at {$date}</>");
            } else {
                $io->note("Working tree clean - nothing to commit");
            }

            // Step 4: Push regardless
            $pushProcess = new Process(['git', 'push', 'origin', 'main']);
            $pushProcess->mustRun();
            $io->writeln("<fg=green>✓ Pushed to origin/main</>");
        } catch (\Throwable $e) {
            $io->error("Push failed: " . $e->getMessage());
            return Command::FAILURE;
        } finally {
            // Always restore original directory
            if ($changedDir) {
                chdir($originalDir);
            }
        }

        $io->success("Push completed for $repositoryName!");
        return Command::SUCCESS;
    }
}
