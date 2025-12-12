<?php

namespace Vigihdev\Command\Exceptions;

use InvalidArgumentException;
use Throwable;

abstract class IOException extends InvalidArgumentException
{

    public function __construct(
        string $message = 'Unexpected IO error.',
        ?string $path = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
