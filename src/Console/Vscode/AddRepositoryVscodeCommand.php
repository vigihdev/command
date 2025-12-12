<?php

declare(strict_types=1);

namespace Vigihdev\Command\Console\Vscode;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument, InputInterface};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Path;
use Vigihdev\Command\Contracts\ExceptionHandlerInterface;
use Vigihdev\Command\DTOs\Repository\{RepositoryDto, RepositoryInfoDto};
use Vigihdev\Command\Exceptions\{ExceptionHandler};
use Vigihdev\Command\Factory\DtoTransformFactory;
use Vigihdev\Command\Validators\{DirectoryValidator, FileValidator, GitDirectoryValidator, GitValidator};
use Vigihdev\Support\{Collection, File};
use Throwable;

#[AsCommand(
    name: 'vscode:add-repo',
    description: '📦 Vscode Add Repository Url'
)]
final class AddRepositoryVscodeCommand extends AbstractVscodeCommand
{

    private ExceptionHandlerInterface $exceptionHandler;

    private string $repoRootPath;
    private string $repoFilepathJson;
    private string $repositoryUrl;

    public function __construct()
    {

        parent::__construct();
        $this->exceptionHandler = new ExceptionHandler();
        $this->repoFilepathJson = Path::join(getenv('ABSPATH'), getenv('REPO_RESOURCE'), 'repository-list-info.json');
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

        try {
            DirectoryValidator::validate(path: $localPathAbs)->mustExist();
            FileValidator::validate(filepath: $this->repoFilepathJson)
                ->mustExist()
                ->mustBeJson();
            GitDirectoryValidator::validate(path: $localPathAbs)
                ->mustBeInitialized()
                ->mustBeValidGitUrl($repositoryUrl);
        } catch (Throwable $e) {
            $this->exceptionHandler->handle($e, $io);
            return Command::FAILURE;
        }

        /** @var RepositoryInfoDto[] $repoDto  */
        $repoDto = DtoTransformFactory::fromFileJson($this->repoFilepathJson, RepositoryInfoDto::class);
        $collection = new Collection($repoDto);

        $this->repositoryUrl = $repositoryUrl;
        $this->repoRootPath = $localPath;

        $this->process($io, $collection);
        return Command::SUCCESS;
    }


    /**
     * @param Collection<RepositoryInfoDto> $collection
     */
    private function process(SymfonyStyle $io, Collection $collection)
    {

        $name = parse_url($this->repositoryUrl)['path'] ?? '';
        $name = preg_replace('/\.\w+$/', '', trim($name, '/'));
        $repository = new RepositoryDto(name: $name, url: $this->repositoryUrl);
        $newDto = new RepositoryInfoDto(repository: $repository, rootPath: $this->repoRootPath);

        $exists = $collection->filter(fn($dto) => $dto->getRepository()->getUrl() === $this->repositoryUrl);
        if ($exists->count() > 0) {
            $localPath = Path::join(getenv('HOME'), $this->repoRootPath);
            $io->warning([
                "Repository {$this->repositoryUrl} Sudah ada.",
                "Root Path : {$exists->first()->getRootPath()}",
                "Name : {$exists->first()->getRepository()->getName()}",
                "Path : {$localPath}",
            ]);
            return;
        }

        $json = $collection
            ->map(fn($dto) => $dto->toArray())
            ->add($newDto->toArray())
            ->toJson();

        if ((bool) File::put($this->repoFilepathJson, $json)) {
            $io->definitionList("Add data in {$this->repoFilepathJson}");

            $io->block([
                'Project Added Successfully!',
            ], 'OK', 'fg=black;bg=green', ' ', true);
        }
    }
}
