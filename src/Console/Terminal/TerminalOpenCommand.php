<?php

declare(strict_types=1);

namespace Vigihdev\Command\Console\Terminal;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument, InputOption, InputInterface};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Process\Process;
use Vigihdev\Command\Contracts\Projects\ProjectInterface;
use Vigihdev\Command\DTOs\Projects\ProjectDto;

#[AsCommand(
    name: 'terminal:open',
    description: '🚀 Open project in Terminal'
)]
final class TerminalOpenCommand extends AbstractTerminalCommand
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
                InputArgument::OPTIONAL,
                'Project name to open in Terminal',
                null,
                $this->getProjectAutocomplete()
            )
            ->setHelp(
                <<<'HELP'
                    The <info>%command.name%</info> command opens a project in Terminal:

                    <info>php %command.full_name% <project-name></info>

                    Available projects will be shown with autocomplete.

                    Examples:
                    <info>php %command.full_name% my-project</info>    Open "my-project" in Terminal
                    <info>php %command.full_name% api-service</info>   Open "api-service" in Terminal
                    HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');

        if (!$name || $name === '.') {
            return $this->openProjectInIterm(new ProjectDto(
                name: 'cwd',
                rootPath: Path::makeRelative(getcwd(), Path::getHomeDirectory())
            ), $io);
        }

        foreach ($this->listProjectNames() as $dto) {
            if ($dto instanceof ProjectInterface && $dto->getName() === $name) {
                return $this->openProjectInIterm($dto, $io);
            }
        }
        $io->error(sprintf('Project "%s" not found!', $name));
        $io->note('Available projects:');

        $projectNames = array_map(
            fn($p) => $p instanceof ProjectInterface ? $p->getName() : '',
            $this->listProjectNames()
        );

        $io->listing(array_filter($projectNames));

        return Command::FAILURE;
    }

    /**
     * Open project in Terminal with proper error handling
     */
    private function openProjectInIterm(ProjectInterface $project, SymfonyStyle $io): int
    {
        $basePath = Path::join(Path::getHomeDirectory(), $project->getRootPath());

        if (!is_dir($basePath)) {
            $io->error(sprintf('Directory does not exist: %s', $basePath));
            return Command::FAILURE;
        }

        $io->section(sprintf('Opening: %s', $project->getName()));
        $io->text(sprintf('Path: <comment>%s</comment>', $basePath));

        try {
            // Gunakan AppleScript untuk lebih reliable membuka tab baru
            $script = $this->generateAppleScript($basePath);
            $process = Process::fromShellCommandline($script);

            $process->setTimeout(10);
            $process->run();

            if ($process->isSuccessful()) {
                $io->success(sprintf('Project "%s" opened in Terminal!', $project->getName()));
                return Command::SUCCESS;
            }

            // Fallback ke command open biasa
            $io->note('Trying alternative method...');
            return $this->openWithFallback($basePath, $io);
        } catch (\Throwable $e) {
            $io->error(sprintf('Failed to open Terminal: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    /**
     * Generate AppleScript untuk buka tab baru di Terminal
     */
    private function generateAppleScript(string $path): string
    {
        $escapedPath = addslashes($path);

        return <<<SCRIPT
        osascript <<'EOF'
        tell application "Terminal"
            activate
            if windows is empty then
                create window with default profile
            else
                tell current window
                    create tab with default profile
                end tell
            end if
            
            tell current session of current window
                write text "cd '$escapedPath' && clear && echo '📁 Project: $(basename \"$escapedPath\")' && pwd && ls -la"
            end tell
        end tell
        EOF
        SCRIPT;
    }

    /**
     * Fallback method jika AppleScript gagal
     */
    private function openWithFallback(string $path, SymfonyStyle $io): int
    {
        try {
            // Method 1: open command
            $process = Process::fromShellCommandline(sprintf('open -a Terminal "%s"', $path));
            $process->run();

            if ($process->isSuccessful()) {
                $io->success('Opened with fallback method!');
                return Command::SUCCESS;
            }

            // Method 2: langsung ke directory
            $process = Process::fromShellCommandline(sprintf('cd "%s" && open -a Terminal .', $path));
            $process->run();

            if ($process->isSuccessful()) {
                $io->success('Opened current directory in Terminal!');
                return Command::SUCCESS;
            }

            $io->error('All methods failed. Please open Terminal manually.');
            $io->text(sprintf('Directory: %s', $path));

            return Command::FAILURE;
        } catch (\Throwable $e) {
            $io->error(sprintf('Fallback also failed: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}
