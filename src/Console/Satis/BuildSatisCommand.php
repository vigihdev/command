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
use Vigihdev\Command\Contracts\SatisClientManagerInterface;

#[AsCommand(
    name: 'satis:build',
    description: 'Build All Satis Run packages with Satis'
)]
final class BuildSatisCommand extends AbstractSatisCommand
{

    public function __construct(
        private readonly SatisClientManagerInterface $satisClient
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {

        $this
            ->addArgument(
                'packages',
                InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                'Package names to build (omit to build all packages)',
                null,
                function () {
                    return $this->getRepositories();
                }
            )
            ->getHelp(
                <<<'HELP'
                The <info>%command.name%</info> command builds specified packages across all Satis services.
                
                <info>php %command.full_name% vendor/package1 vendor/package2</info>
                
                This will search for the packages in all available Satis services and build them.
                HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $packages = $input->getArgument('packages');

        // handle empty packages search local composer
        $composerJson = Path::join(getcwd() ?? '', 'composer.json');
        if (empty($packages) && is_file($composerJson)) {
            $composer = json_decode(file_get_contents($composerJson));
            $packages[] = $composer->name;
        }

        if (empty($packages)) {
            $io->error("Tidak ada package untuk di build");
            return Command::FAILURE;
        }

        $io->title('Satis Build Process');
        $io->writeln(
            sprintf('Searching for packages: <info>%s</info>', implode(', ', $packages))
        );

        $availablePackages = $this->findAvailablePackages($packages, $io);

        if (empty($availablePackages)) {
            $io->warning('No matching packages found in any Satis service.');
            return Command::SUCCESS;
        }

        $io->success(sprintf(
            'Found %d package(s) across %d service(s)',
            count(array_merge(...array_values($availablePackages))),
            count($availablePackages)
        ));

        foreach ($availablePackages as $serviceName => $servicePackages) {
            $command = "satis:{$serviceName}:build";
            $io->section("Building with: <info>{$command}</info>");

            foreach ($servicePackages as $package) {
                $io->writeln(" - Building package: <comment>{$package}</comment>");
                $this->executeBuildCommand($command, $package, $io);
            }
        }

        $io->success('All builds completed!');
        return Command::SUCCESS;
    }

    private function executeBuildCommand(string $command, string $package, SymfonyStyle $io): void
    {
        try {
            $process = new Process(['console', $command, $package]);
            $process->setTimeout(300); // 5 minutes timeout
            $process->run();

            // Real-time output
            if ($output = $process->getOutput()) {
                $lines = explode("\n", trim($output));
                foreach ($lines as $line) {
                    if (!empty(trim($line))) {
                        $io->writeln("   <fg=blue>[{$package}]</> {$line}");
                    }
                }
            }

            if ($errorOutput = $process->getErrorOutput()) {
                $lines = explode("\n", trim($errorOutput));
                foreach ($lines as $line) {
                    if (!empty(trim($line))) {
                        $io->writeln("   <fg=yellow>[{$package}] WARNING:</> {$line}");
                    }
                }
            }

            if (!$process->isSuccessful()) {
                $io->error("Build failed for {$package} (Exit code: {$process->getExitCode()})");
            }
        } catch (\Throwable $e) {
            $io->error("Build failed for {$package}: " . $e->getMessage());
        }
    }

    private function findAvailablePackages(array $packages, SymfonyStyle $io): array
    {
        $availablePackages = [];
        $foundCount = 0;

        foreach ($this->satisClient->getAvailableServiceNames() as $serviceName) {
            $io->writeln(sprintf('Searching in service: <comment>%s</comment>', $serviceName));

            $satisJson = $this->satisClient->getClient($serviceName)->getSatisJson();
            $repos = $this->extractPackageNames($satisJson->getRepositories());

            $servicePackages = [];
            foreach ($packages as $package) {
                if (in_array($package, $repos, true)) {
                    $servicePackages[] = $package;
                    $foundCount++;
                    $io->writeln(sprintf('   ✓ Found: <info>%s</info>', $package));
                }
            }

            if (!empty($servicePackages)) {
                $availablePackages[$serviceName] = $servicePackages;
            }
        }

        if ($foundCount > 0) {
            $io->success(sprintf('Total packages found: %d', $foundCount));
        }

        return $availablePackages;
    }

    private function extractPackageNames(array $repositories): array
    {
        return array_map(
            fn($repo) => str_replace(
                ['git@github.com:', 'github.com:', 'https://github.com/', 'http://github.com/', '.git'],
                '',
                $repo->getUrl()
            ),
            $repositories
        );
    }

    private function getRepositories(): array
    {

        $repositories = [];
        foreach ($this->satisClient->getAvailableServiceNames() as $serviceName) {
            $satisJson = $this->satisClient->getClient($serviceName)->getSatisJson();
            $repos = $this->extractPackageNames($satisJson->getRepositories());
            array_push($repositories, ...$repos);
        }

        return array_unique($repositories);
    }
}
