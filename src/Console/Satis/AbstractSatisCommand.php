<?php

declare(strict_types=1);

namespace Vigihdev\Command\Console\Satis;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Path;

abstract class AbstractSatisCommand extends Command
{

    /**
     *
     * @return string
     * @throws RuntimeException
     */
    protected function getSshSatisThrubus(): string
    {

        $sshHost    = getenv('SATIS_THRUBUS_HOST');
        $remotePath = getenv('SATIS_THRUBUS_PATH');
        $phpPath    = getenv('SATIS_THRUBUS_PHP_PATH');
        $binSatis   = 'bin/satis ';
        if ($sshHost && $remotePath && $phpPath) {
            $cmd = sprintf(
                "ssh %s 'cd %s && %s %s'",
                escapeshellarg($sshHost),
                escapeshellarg($remotePath),
                escapeshellarg($phpPath),
                escapeshellarg($binSatis)
            );
            return $cmd;
        }

        throw new \RuntimeException("Satis Thrubus configuration is not set properly in .env file.");
    }

    protected function getPackages(string $filename): array
    {

        $filepath = Path::join(getenv('ABSPATH'), getenv('SATIS_RESOURCE'), "{$filename}.json");
        if (!file_exists($filepath)) {
            throw new \RuntimeException("Packages file not found: $filepath");
        }

        $packages = file_get_contents($filepath);
        $packages = json_decode($packages, true);
        return is_array($packages) ? $packages : [];
    }
}
