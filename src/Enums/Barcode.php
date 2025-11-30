<?php

namespace Zpl\Enums;

class Barcode
{
    const AZTEK = 'B0';
    const CODE11 = 'B1';
    const INTERLEAVED2 = 'B2';
    const CODE39 = 'B3';
    const CODE49 = 'B4';
    const PLANET = 'B5';
    const PDF417 = 'B7';
    const EAN8 = 'B8';
    const UPCE = 'B9';
    const CODE93 = 'BA';
    const CODABLOCK = 'BB';
    const CODE128 = 'BC';
    const UPS = 'BD';
    const EAN13 = 'BE';
    const MICROPDF417 = 'BF';
    const INDUSTRIAL2 = 'BI';
    const STANDARD2 = 'BJ';
    const ANSI = 'BK';
    const LOGMARS = 'BL';
    const MSI = 'BM';
    const PLESSEY = 'BP';
    const QR = 'BQ';
    const GS1 = 'BR';
    const UPC_EAN = 'BS';
    const TLC39 = 'BT';
    const UPCA = 'BU';
    const DATAMATRIX = 'BX';
    const DEFAULT = 'BY';
    const POSTAL = 'BZ';

    /** @return array<string> **/
    public static function values(): array
    {
        return (new \ReflectionClass(static::class))->getConstants();
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

        throw new \InvalidArgumentException('Valid values for barcode type are: ' . implode(',', $values));
    }
}
