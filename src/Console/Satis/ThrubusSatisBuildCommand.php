<?php

declare(strict_types=1);

namespace Vigihdev\Command\Console\Satis;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument, InputOption, InputInterface};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'satis:thrubus:build',
    description: 'Build Thrubus packages with Satis'
)]
final class ThrubusSatisBuildCommand extends AbstractSatisCommand
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
                    return $this->getPackages('thrubus');
                }
            )
            ->setHelp(
                <<<'HELP'
                    The <info>%command.name%</info> command builds Thrubus packages for Satis repository.

                    <comment>Build all thrubus packages:</comment>
                    <info>%command.full_name%</info>

                    <comment>Build specific packages:</comment>
                    <info>%command.full_name% thrubus/wp-thrubus-bundle thrubus/models</info>

                    HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $packages = $input->getArgument('packages');

        if (is_array($packages)) {
            foreach ($packages as $package) {
                $io->section("Building package: <info>{$package}</info>");
                $cmd = $this->buildCommand($package);
                $process = Process::fromShellCommandline($cmd);

                try {
                    $process->mustRun();

                    if ($process->getOutput()) {
                        $io->writeln("<fg=green>{$process->getOutput()}</>");
                    }

                    if ($process->getErrorOutput()) {
                        $io->warning($process->getErrorOutput());
                    }
                } catch (\Throwable $e) {
                    $io->error("Build failed: " . $e->getMessage());
                }
            }
            return Command::SUCCESS;
        }

        $io->error("Not suport {$packages}");
        return Command::FAILURE;
    }


    private function buildCommand(string $packages): string
    {
        $repository = "https://github.com/{$packages}.git";
        $cmd = sprintf(
            "{$this->getSshSatisThrubus()} %s",
            escapeshellarg("build --repository-url={$repository}")
        );
        return $cmd;
    }
}
