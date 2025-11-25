<?php

declare(strict_types=1);

namespace Vigihdev\Command\Console\Repository;

use GuzzleHttp\Client;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Path;
use Vigihdev\Encryption\Contracts\EnvironmentEncryptorServiceContract;
use VigihDev\SymfonyBridge\Config\Service\ServiceLocator;

abstract class AbstractRepositoryCommand extends Command
{

    private ?EnvironmentEncryptorServiceContract $serviceEncryptor = null;

    public function __construct(
        ?string $name = null,
    ) {

        if (! $this->serviceEncryptor) {
            if (! ServiceLocator::has(EnvironmentEncryptorServiceContract::class)) {
                throw new RuntimeException("EnvironmentEncryptorServiceContract service not found in ServiceLocator");
            }
            $this->serviceEncryptor = ServiceLocator::get(EnvironmentEncryptorServiceContract::class);
        }
        parent::__construct($name);
    }

    /**
     *
     * @return array<string,string>
     */
    protected function getAuthRepositoryMap(): array
    {

        $filepath = Path::join(getenv('ABSPATH'), getenv('REPO_RESOURCE'), "auth.json");
        if (!file_exists($filepath)) {
            throw new \RuntimeException("Repository auth file not found: $filepath");
        }

        $packages = file_get_contents($filepath);
        $packages = json_decode($packages, true);
        return is_array($packages) ? $packages : [];
    }

    protected function getListRepo(string $username, string $token): array
    {

        $token = $this->serviceEncryptor->isEncrypted($token)
            ? $this->serviceEncryptor->decrypt($token)
            : $token;

        $client = new Client([
            'base_uri' => 'https://api.github.com/',
            'timeout'  => 120,
        ]);

        try {
            $response = $client->request('GET', "user/repos", [
                'query' => [
                    'visibility' => 'all',
                    'affiliation' => 'owner,collaborator,organization_member',
                    'sort' => 'updated',
                    'per_page' => 100,
                    'page' => 1
                ],
                'headers' => [
                    'Accept'          => 'application/vnd.github+json',
                    'Authorization'   => "Bearer {$token}",
                    'X-GitHub-Api-Version' => '2022-11-28',
                ],
            ]);

            $repositories = json_decode($response->getBody()->getContents(), true);
            return is_array($repositories) ? $repositories : [];
        } catch (\Exception $e) {
            throw new RuntimeException(
                "Failed to fetch repositories for user {$username}: " . $e->getMessage()
            );
        }
    }
}
