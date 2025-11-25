<?php

declare(strict_types=1);

namespace Vigihdev\Command\Console\Repository;

use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument, InputOption, InputInterface};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'repo:list',
    description: '📦 List all GitHub repositories with detailed information'
)]
final class ListRepositoryCommand extends AbstractRepositoryCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'GitHub username or organization name',
                null,
                function () {
                    return array_keys($this->getAuthRepositoryMap());
                }
            )
            ->addOption(
                'format',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Output format (table, json, simple)',
                'table'
            )
            ->addOption(
                'filter',
                null,
                InputOption::VALUE_OPTIONAL,
                'Filter repositories by type (all, owner, collaborator, organization, public, private)',
                'all'
            )
            ->addOption(
                'limit',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Limit number of repositories to display',
                50
            )
            ->setHelp(
                <<<'HELP'
                    The <info>repo:list</info> command displays all GitHub repositories for a user or organization in a formatted table.

                    <comment>Usage examples:</comment>
                    <info>console repo:list thrubus</info>
                    <info>console repo:list thrubus --filter=private</info>
                    <info>console repo:list thrubus --format=json --limit=10</info>
                    <info>console repo:list thrubus --filter=owner --format=simple</info>

                    <comment>Available filters:</comment>
                    • <info>all</info>          - All repositories (default)
                    • <info>owner</info>        - Only repositories owned by user
                    • <info>collaborator</info> - Only repositories where user is collaborator
                    • <info>organization</info> - Only organization repositories
                    • <info>public</info>       - Only public repositories
                    • <info>private</info>      - Only private repositories

                    <comment>Available formats:</comment>
                    • <info>table</info>  - Formatted table with columns (default)
                    • <info>json</info>   - JSON output for machine processing
                    • <info>simple</info> - Simple list with repo names only
                    HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');
        $format = $input->getOption('format');
        $filter = $input->getOption('filter');
        $limit = (int) $input->getOption('limit');

        $auths = $this->getAuthRepositoryMap();

        if (!isset($auths[$username]) || !is_string($auths[$username])) {
            $io->error("Auth token for user '$username' not found in configuration");
            return Command::FAILURE;
        }

        try {
            $io->section("Fetching repositories for: {$username}");
            $allRepositories = $this->getListRepo($username, $auths[$username]);
            $filteredRepositories = $this->filterRepositories($allRepositories, $username, $filter);

            // Apply limit
            if ($limit > 0 && count($filteredRepositories) > $limit) {
                $filteredRepositories = array_slice($filteredRepositories, 0, $limit);
                $io->note(sprintf('Showing first %d repositories (use --limit for more)', $limit));
            }

            $this->displayRepositories($io, $filteredRepositories, $username, $format);

            // Summary
            $io->writeln('');
            $io->success(sprintf(
                'Displayed %d of %d repositories (%s)',
                count($filteredRepositories),
                count($allRepositories),
                $filter === 'all' ? 'total' : $filter
            ));
        } catch (\Throwable $e) {
            throw new RuntimeException(
                "Failed to fetch repositories for user '{$username}': " . $e->getMessage(),
                previous: $e
            );
        }

        return Command::SUCCESS;
    }

    private function filterRepositories(array $repositories, string $username, string $filter): array
    {
        return array_filter($repositories, function ($repo) use ($username, $filter) {
            $isOwner = $repo['owner']['login'] === $username;
            $isOrganization = $repo['owner']['type'] === 'Organization';
            $isPrivate = $repo['private'];

            return match ($filter) {
                'owner' => $isOwner,
                'collaborator' => !$isOwner && !$isOrganization,
                'organization' => $isOrganization,
                'public' => !$isPrivate,
                'private' => $isPrivate,
                default => true // 'all'
            };
        });
    }

    private function displayRepositories(SymfonyStyle $io, array $repositories, string $username, string $format): void
    {
        if (empty($repositories)) {
            $io->warning('No repositories found matching the specified filter');
            return;
        }

        switch ($format) {
            case 'json':
                $io->writeln(json_encode($repositories, JSON_PRETTY_PRINT));
                break;

            case 'simple':
                foreach ($repositories as $repo) {
                    $io->writeln($repo['full_name']);
                }
                break;

            case 'table':
            default:
                $this->displayTable($io, $repositories, $username);
                break;
        }
    }

    private function displayTable(SymfonyStyle $io, array $repositories, string $username): void
    {
        $tableData = [];

        foreach ($repositories as $repo) {
            $type = '👑 Owner';
            if ($repo['owner']['login'] !== $username) {
                $type = $repo['owner']['type'] === 'Organization' ? '🏢 Org' : '🤝 Collab';
            }

            $visibility = $repo['private'] ? '🔒 Private' : '🌐 Public';
            $updated = date('Y-m-d', strtotime($repo['updated_at']));
            $size = $this->formatSize($repo['size'] * 1024); // Convert from KB

            $tableData[] = [
                'name' => $repo['name'],
                'full_name' => $repo['full_name'],
                'type' => $type,
                'visibility' => $visibility,
                'language' => $repo['language'] ?? 'N/A',
                // 'stars' => $repo['stargazers_count'],
                // 'forks' => $repo['forks_count'],
                // 'issues' => $repo['open_issues_count'],
                'size' => $size,
                'updated' => $updated,
            ];
        }

        $io->table(
            [
                'Name',
                'Full Name',
                'Type',
                'Visibility',
                'Language',
                // '⭐ Stars',
                // '🍴 Forks',
                // '🐛 Issues',
                '📦 Size',
                '🕒 Updated'
            ],
            $tableData
        );
    }

    private function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
