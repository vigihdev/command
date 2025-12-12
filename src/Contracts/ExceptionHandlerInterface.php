<?php

declare(strict_types=1);

namespace Vigihdev\Command\Contracts;

use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

interface ExceptionHandlerInterface
{
    /**
     * Memproses dan memformat Throwable/Exception ke output command.
     * Metode ini harus mencetak pesan error dan mengembalikan status kegagalan.
     *
     * @param Throwable $e Pengecualian yang dilempar.
     * @param SymfonyStyle $io Style IO (untuk mencetak output).
     * @return int Status keluar command (misalnya, Command::FAILURE).
     */
    public function handle(Throwable $e, SymfonyStyle $io): int;
}
