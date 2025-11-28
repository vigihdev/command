<?php

declare(strict_types=1);

namespace Vigihdev\Command\Console\Composer;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Process\Process;

abstract class AbstractComposerCommand extends Command
{

    protected function validatePathComposer(): bool
    {
        return is_dir(Path::join(getcwd() ?? '', 'vendor', 'composer'));
    }

    protected function getShowPackages(): array
    {
        $outs = [];
        if (!$this->validatePathComposer()) {
            return $outs;
        }
        try {
            $process = new Process(['composer', 'show', '--format=json']);
            $process->mustRun();
            $outs = $process->getOutput();
            $packages = json_decode($process->getOutput(), true);
            return array_map(function ($item) {
                return $item['name'] ?? '';
            }, $packages['installed'] ?? []);
        } catch (\Throwable $e) {
            return $outs;
        }
    }
}
