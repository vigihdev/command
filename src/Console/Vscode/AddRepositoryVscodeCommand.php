<?php

declare(strict_types=1);

namespace Vigihdev\Command\Console\Vscode;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument, InputOption, InputInterface};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Path;
use Throwable;
use Vigihdev\Command\Contracts\ExceptionHandlerInterface;
use Vigihdev\Command\DTOs\Repository\RepositoryInfoDto;
use Vigihdev\Command\Exceptions\{ExceptionHandler};
use Vigihdev\Command\Factory\DtoTransformFactory;
use Vigihdev\Command\Validators\{DirectoryValidator, FileValidator, GitDirectoryValidator, GitValidator};
use Vigihdev\Support\{Collection, File};

#[AsCommand(
    name: 'vscode:add-repo',
    description: '📦 Vscode Add Repository Url'
)]
final class AddRepositoryVscodeCommand extends AbstractVscodeCommand
{

    private ExceptionHandlerInterface $exceptionHandler;
    public function __construct()
    {

        parent::__construct();
        $this->exceptionHandler = new ExceptionHandler();
    }

    protected function configure(): void
    {
        $this
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

        $localPathAbs = Path::join(getenv('HOME') ?? '', $localPath);
        $filepathRepo = Path::join(getenv('ABSPATH'), getenv('REPO_RESOURCE'), 'repository-list-info.json');

        try {
            DirectoryValidator::validate(path: $localPathAbs)->mustExist();
            FileValidator::validate(filepath: $filepathRepo)->mustExist();
            GitDirectoryValidator::validate(path: $localPathAbs)->mustBeInitialized();
            // GitValidator::validate(path: $repositoryUrl)->mustBeValidUrl();
        } catch (Throwable $e) {
            $this->exceptionHandler->handle($e, $io);
            return Command::FAILURE;
        }

        /** @var RepositoryInfoDto[] $repoDto  */
        $repoDto = DtoTransformFactory::fromFileJson($filepathRepo, RepositoryInfoDto::class);
        $collection = new Collection($repoDto);

        $this->process($io, $localPath, $repositoryUrl);

        return Command::SUCCESS;
    }

    private function process(SymfonyStyle $io, Collection $collection) {}
}
