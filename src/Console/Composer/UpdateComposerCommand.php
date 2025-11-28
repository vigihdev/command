<?php

declare(strict_types=1);

namespace Vigihdev\Command\Console\Composer;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument, InputOption, InputInterface};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Process\Process;
use Vigihdev\Command\Console\Composer\AbstractComposerCommand;
use Vigihdev\Support\File;

#[AsCommand(
    name: 'composer:update',
    description: 'Update Package Composer'
)]
final class UpdateComposerCommand extends AbstractComposerCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'packages',
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'Package names to build (omit to build all thrubus packages)',
                null,
                function () {
                    return $this->getShowPackages();
                }
            )
            ->setHelp(
                <<<'HELP'
                    HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $packages = $input->getArgument('packages');

        if (! is_array($packages) || (is_array($packages) && count($packages) === 0)) {
            $io->error("Out format of packages");
            return Command::FAILURE;
        }

        if (! $this->validatePathComposer()) {
            $io->error("Not found composer.json");
            return Command::FAILURE;
        }

        $command = array_merge(['composer', 'update'], $packages);
        $process = new Process($command);

        try {
            $process->mustRun();

            if ($process->getOutput()) {
                $io->writeln($process->getOutput());
            }

            if ($process->getErrorOutput()) {
                $io->writeln($process->getErrorOutput());
            }
        } catch (\Throwable $e) {
            $io->error("Build failed: " . $e->getMessage());
        }

        return Command::SUCCESS;
    }
}
