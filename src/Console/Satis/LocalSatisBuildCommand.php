<?php

declare(strict_types=1);

namespace Vigihdev\Command\Console\Satis;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument, InputOption, InputInterface};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'satis:local:build',
    description: 'Build Local packages with Satis'
)]
final class LocalSatisBuildCommand extends AbstractSatisCommand
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
                    return $this->getPackages('local');
                }
            )
            ->getHelp(
                <<<'HELP'
                HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $io = new SymfonyStyle($input, $output);
        $packages = $input->getArgument('packages');

        if (is_array($packages)) {
            foreach ($packages as $package) {

                $satisPath = Path::join(getenv('HOME_PATH') ?? '', getenv('SATIS_LOCAL_PATH') ?? '');
                if (! is_dir($satisPath)) {
                    $io->error("Satis path not found: {$satisPath}");
                    return Command::FAILURE;
                }

                $io->section("Building package: <info>{$package}</info>");
                $repository = "https://github.com/{$package}.git";
                $cmd = sprintf(
                    "cd {$satisPath} && bin/satis build --repository-url=%s",
                    $repository
                );
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
}
