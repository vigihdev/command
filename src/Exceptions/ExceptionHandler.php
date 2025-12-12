<?php

declare(strict_types=1);

namespace Vigihdev\Command\Exceptions;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Vigihdev\Command\Contracts\ExceptionHandlerInterface;
use Throwable;

final class ExceptionHandler implements ExceptionHandlerInterface
{
    public function handle(Throwable $e, SymfonyStyle $io): int
    {
        if ($e instanceof IOException) {
            $io->error("IO Failure: " . $e->getMessage());
            return Command::FAILURE;
        }

        if ($e instanceof \RuntimeException) {
            $io->error(["Runtime Error: ", $e->getMessage()]);
            return Command::FAILURE;
        }

        $io->error("An unexpected error occurred: " . $e->getMessage());
        return Command::FAILURE;
    }
}
