# `^BY` — Barcode Default (Module Width)

## Overview

The `^BY` (Bar Code Default Parameters) command sets the default width for barcode modules (the thinnest element). It acts as a multiplier that scales all bar widths in the following barcode command.

The library emits `^BY` automatically when you pass a non-zero `$size` parameter to `drawBarcode()`, `drawCode128()`, or `drawCode39()`.

---

## ZPL Syntax

```
^BYw,r,h
```

| Parameter | Description | Range | Default |
|-----------|-------------|-------|---------|
| `w` | Module width multiplier | `1`–`9` | `2` |
| `r` | Wide bar to narrow bar ratio | `2.0`–`3.0` | `3.0` |
| `h` | Default barcode height in dots | — | — |

---

## PHP API

`^BY` is emitted automatically by barcode methods when `$size > 0`:

```php
$builder->drawBarcode(
    Barcode $type,
    float $x,
    float $y,
    float $height,
    string $data,
    bool $printData = false,
    bool $labelAbove = false,
    Orientation $orientation = Orientation::NORMAL,
    int $size = 0   // ← triggers ^BY when > 0
): void

$builder->drawCode128(float $x, float $y, float $height, string $data, bool $printData, Orientation $orientation, int $size = 0): void
$builder->drawCode39(float $x, float $y, float $height, string $data, bool $printData, Orientation $orientation, int $size = 0): void
```

To emit `^BY` directly:

```php
$builder->addCommand('BY', 3);
// Output: ^BY3
```

---

## Examples

### Narrow barcode (size 1)

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::DOTS);
$builder->drawCode128(5, 50, 20, 'ABC123', false, \Zpl\Enums\Orientation::NORMAL, 1);
echo $builder->toZpl();
```

Output:

```
^XA
^FO5,50
^BY1
^BCN,20,N,N,N,A
^FDABC123^FS
^XZ
```

### Wide barcode (size 5)

```php
$builder->drawCode128(5, 50, 40, 'ABC123', true, \Zpl\Enums\Orientation::NORMAL, 5);
// Emits: ^BY5 before the ^BC command
```

### Raw command

```php
$builder->addCommand('BY', 2);
// Output: ^BY2
```

---

## Notes

- Valid `$size` values are `1`–`9`; values of `0` or below suppress `^BY` emission.
- A higher width multiplier produces a wider, easier-to-scan barcode but occupies more horizontal space.
- `^BY` applies only to the immediately following barcode command.
