<?php

namespace Zpl\Enums;

class Alignment
{
    const LEFT = 'L';
    const CENTER = 'C';
    const RIGHT = 'R';
    const JUSTIFIED = 'J';

    /** @return array<string> **/
    public static function values(): array
    {
        return ['L', 'C', 'R', 'J'];
    }

    public static function isValid(string $value): bool
    {
        return in_array($value, static::values(), true);
    }

    /** @throws \InvalidArgumentException */
    public static function validate(string $value): void
    {
        $values = self::values();
        if (in_array($value, $values, true)) {
            return;
        }

        throw new \InvalidArgumentException('Valid values for alignment are: ' . implode(',', $values));
    }
}
