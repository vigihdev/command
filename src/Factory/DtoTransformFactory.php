<?php

declare(strict_types=1);

namespace Vigihdev\Command\Factory;

use RuntimeException;
use Serializer\Factory\JsonTransformerFactory;
use Throwable;

final class DtoTransformFactory
{
    public static function fromFileJson(string $filepath, string $dtoClass)
    {

        try {
            $json = file_get_contents($filepath);
            $factory = JsonTransformerFactory::create($dtoClass);
            $json = trim($json);
            $json = substr($json, 0, 1) === '[' ? $json : "[{$json}]";
            return $factory->transformArrayJson($json);
        } catch (Throwable $e) {
            throw new RuntimeException(
                sprintf("Gagal transformer from file : %s %s", "{$filepath}", $e->getMessage())
            );
        }
    }
}
