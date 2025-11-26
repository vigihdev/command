<?php

declare(strict_types=1);

namespace Vigihdev\Command\Http;

use GuzzleHttp\Client;
use RuntimeException;
use Serializer\Factory\JsonTransformerFactory;
use Vigihdev\Command\Contracts\ClientSatisInterface;
use Vigihdev\Command\Contracts\Satis\SatisJsonInterface;
use Vigihdev\Command\DTOs\Satis\SatisJsonDto;
use Vigihdev\Encryption\Contracts\EnvironmentEncryptorServiceContract;

final class SatisClient
{


    public function __construct(
        private readonly ClientSatisInterface $client,
        private readonly EnvironmentEncryptorServiceContract $encryptor,
    ) {}

    public function getPackagesJson(): array
    {

        $client = new Client([
            'base_uri' => $this->decrypt($this->client->getBaseUri()),
            'timeout'  => $this->client->getTimeout(),
            'auth' => [
                $this->decrypt($this->client->getAuth()->getUsername()),
                $this->decrypt($this->client->getAuth()->getPassword())
            ]
        ]);

        $response = $client->request('GET', "package.json");
        $repositories = json_decode($response->getBody()->getContents(), true);
        return is_array($repositories) ? $repositories : [];
    }

    public function getSatisJson(): SatisJsonInterface
    {

        try {

            $client = new Client([
                'base_uri' => $this->decrypt($this->client->getBaseUri()),
                'timeout'  => $this->client->getTimeout(),
                'auth' => [
                    $this->decrypt($this->client->getAuth()->getUsername()),
                    $this->decrypt($this->client->getAuth()->getPassword())
                ]
            ]);

            $response = $client->request('GET', "satis.json");
            $jsonData = $response->getBody()->getContents();
            $jsonTransformer = JsonTransformerFactory::create(SatisJsonDto::class);
            return $jsonTransformer->transformJson($jsonData);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Error fetching or transforming satis.json: ' . $e->getMessage());
        }
    }


    /**
     *
     * @param string $value
     * @return string
     * @throws RuntimeException
     */
    private function decrypt(string $value): string
    {
        if (! $this->encryptor) {
            throw new \RuntimeException('Encryptor service not found');
        }

        return $this->encryptor->isEncrypted($value)
            ? $this->encryptor->decrypt($value)
            : $value;
    }
}
