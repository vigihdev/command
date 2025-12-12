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
    name: 'vscode:add-project',
    description: '📦 Vscode Add Project Directory'
)]
final class AddProjectVscodeCommand extends AbstractVscodeCommand
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
            )
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'Project specific by name',
                null,
            )
            ->addOption(
                'label',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Project specific by name',
                ProjectEnum::PROJECT->value,
                function () {
                    return array_column(ProjectEnum::cases(), 'value');
                },
            )
            ->setHelp(
                <<<'HELP'
                    <info>Menambahkan proyek baru ke VSCode Project Manager</info>

                    <comment>Penggunaan:</comment>
                    %command.full_name% NAMA PATH [--label=LABEL]

                    <comment>Contoh:</comment>
                    %command.full_name% my_project /path/to/project
                    %command.full_name% my_project ./work-project --label=work
                    %command.full_name% my_project ../personal -l personal

                    <comment>Label yang tersedia:</comment>
                    • project (default)
                    • work
                    • personal

                    <comment>Catatan:</comment>
                    • Nama proyek harus unik
                    • Path bisa absolut atau relatif
                    • Label menentukan kategori proyek
                HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $io = new SymfonyStyle($input, $output);
        $projectName = $input->getArgument('name');
        $path = $input->getArgument('path');
        $label = $input->getOption('label');

        $pathAbsolute = Path::join(getenv('HOME') ?? '', $path);
        $filepathLabel = Path::join(getenv('ABSPATH'), getenv('VSCODE_RESOURCE'), "{$label}.json");
        try {
            DirectoryValidator::validate(path: $pathAbsolute)->mustExist();
            FileValidator::validate(filepath: $filepathLabel)->mustExist();
        } catch (DirectoryException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        } catch (FileException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }
        $this->process($io, $projectName, $label, $path, $filepathLabel);

        return Command::SUCCESS;
    }

    private function process(SymfonyStyle $io, string $projectName, string $label, string $path, string $filepathLabel): void
    {

        $items = json_decode(File::get($filepathLabel), true);
        $collection = new Collection($items);
        $exists = $collection
            ->filter(fn($v, $k) => $v === $path && $k === $projectName)
            ->count();

        if ($exists > 0) {
            $io->warning([
                "Duplicate from {$projectName} : {$path}",
                "Path : {$filepathLabel}",
            ]);
            return;
        }

        $data = array_merge($collection->toArray(), [$projectName => $path]);
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ((bool) File::put($filepathLabel, $json)) {
            $io->definitionList("Add data in {$filepathLabel}");

            $io->block([
                'Project Added Successfully!',
                "Name:  {$projectName}",
                "Path:  {$path}",
                "Label: {$label}"
            ], 'OK', 'fg=black;bg=green', ' ', true);
        }
    }
}
