<?php

declare(strict_types=1);

namespace Vigihdev\Command\DTOs;

use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

abstract class BaseDto
{
    public function toArray(): array
    {

        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
        $properties = empty($properties) ? $reflection->getProperties(ReflectionProperty::IS_PRIVATE) : $properties;


        $result = [];
        foreach ($properties as $property) {
            $value = $property->getValue($this);
            if ($value instanceof self) {
                $value = $value->toArray();
            }

            if (is_object($value) && !$value instanceof self) {
                $values = [];
                foreach ($this->getReflectionPropertys($value) as $prop) {
                    $values[$prop->getName()] = $prop->getValue($value);
                }
                $value = $values;
            }

            $result[$property->getName()] = $value;
        }

        return $result;
    }

    /**
     * @param string|object $objectOrClass
     * @return array<int,ReflectionProperty>
     */
    private function getReflectionPropertys(object|string $objectOrClass): array
    {
        try {
            $reflection = new ReflectionClass($objectOrClass);
            $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
            $properties = empty($properties) ? $reflection->getProperties(ReflectionProperty::IS_PRIVATE) : $properties;
            return $properties;
        } catch (\Throwable $e) {
            throw new ReflectionException("Class {$objectOrClass} tidak tersedia.");
        }
    }

    public function toJson(int $flags = 0, int $depth = 512): string
    {
        return json_encode($this->toArray(), $flags | JSON_THROW_ON_ERROR, $depth);
    }

    public function __toString(): string
    {
        return $this->toJson(flags: JSON_FORCE_OBJECT);
    }
}
