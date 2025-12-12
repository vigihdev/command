<?php

declare(strict_types=1);

namespace Vigihdev\Command\Console\Vscode;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument, InputOption, InputInterface};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Path;
use Vigihdev\Command\Enums\ProjectEnum;
use Vigihdev\Command\Exceptions\IO\{DirectoryException, FileException};
use Vigihdev\Command\Validators\{DirectoryValidator, FileValidator};
use Vigihdev\Support\{Collection, File};

#[AsCommand(
    name: 'vscode:add-repo',
    description: '📦 Vscode Add Repository Url'
)]
final class AddRepositoryVscodeCommand extends AbstractVscodeCommand
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
                'Nama proyek yang akan ditambahkan',
                null,
            )
            ->addArgument(
                'local-path',
                InputArgument::REQUIRED,
                'Path lokal untuk menyimpan repository',
                null,
            )
            ->addArgument(
                'repository-url',
                InputArgument::REQUIRED,
                'URL repository Git (HTTPS/SSH)',
                null,
            )
            ->setHelp(
                <<<'HELP'
                    Menambahkan repository Git ke daftar proyek VSCode.

                    <comment>Penggunaan:</comment>
                    php %command.full_name% NAMA PATH_LOCAL REPOSITORY_URL

                    <comment>Contoh:</comment>
                    php %command.full_name% "My App" ./myapp https://github.com/user/repo.git
                    php %command.full_name% "Work Project" /projects/work git@github.com:company/project.git

                    <comment>Catatan:</comment>
                    • Pastikan Git terinstall
                    • Nama proyek harus unik
                    • Proyek akan ditambahkan dengan label "project"

                 HELP
            );
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $io = new SymfonyStyle($input, $output);
        $localPath = $input->getArgument('local-path');
        $repositoryUrl = $input->getArgument('repository-url');

        $localPathAbsolute = Path::join(getenv('HOME') ?? '', $localPath);
        $filepathRepo = Path::join(getenv('ABSPATH'), getenv('REPO_RESOURCE'), 'repository-list-info.json');

        try {
            DirectoryValidator::validate(path: $localPathAbsolute)->mustExist();
            FileValidator::validate(filepath: $filepathRepo)->mustExist();
        } catch (DirectoryException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        } catch (FileException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        // $this->process($io, $projectName, $label, $path, $filepathLabel);

        return Command::SUCCESS;
    }

    private function process() {}
}
