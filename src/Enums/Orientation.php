<?php

namespace Zpl\Enums;

class Orientation
{
    const NORMAL = 'N'; // normal as default
    const ROTATED = 'R'; // rotated 90 degrees
    const INVERTED = 'I'; // inverted 180 degrees
    const BOTTOM_UP = 'B'; // bottom-up 270 degrees, read from bottom up

    /** @return array<string> **/
    public static function values(): array
    {
        return ['N', 'R', 'I', 'B'];
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

        throw new \InvalidArgumentException('Valid values for orientation are: ' . implode(',', $values));
    }
}
