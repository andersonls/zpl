# `^BC` — Code 128 Barcode

## Overview

The `^BC` (Bar Code 128) command prints a Code 128 barcode. Code 128 is a high-density, alphanumeric linear barcode that supports the full ASCII character set and is widely used in logistics, shipping, and inventory management.

---

## ZPL Syntax

```
^BCo,h,f,g,e,m
```

| Parameter | Description | Values | Default |
|-----------|-------------|--------|---------|
| `o` | Orientation | `N` normal, `R` rotated 90°, `I` inverted, `B` bottom-up | `N` |
| `h` | Barcode height in dots | — | — |
| `f` | Print interpretation line (human-readable) | `Y` / `N` | `Y` |
| `g` | Print interpretation line above barcode | `Y` / `N` | `N` |
| `e` | UCC check digit | `Y` / `N` | `N` |
| `m` | Mode | `N` no selected mode, `U` UCC Case, `A` automatic, `D` UCC/EAN | `N` |

---

## PHP API

### `drawCode128()` — convenience wrapper

```php
$builder->drawCode128(
    float $x,
    float $y,
    float $height,
    string $data,
    bool $printData = false,
    Orientation $orientation = Orientation::NORMAL,
    int $size = 0
): void
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$x` | `float` | X position in the current unit. |
| `$y` | `float` | Y position in the current unit. |
| `$height` | `float` | Barcode height in the current unit. |
| `$data` | `string` | Data to encode. |
| `$printData` | `bool` | Print human-readable text below the barcode. |
| `$orientation` | `Orientation` | Rotation of the barcode. |
| `$size` | `int` | Module width multiplier (1–9); `0` uses the printer default. |

### `drawBarcode()` — generic wrapper

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
    int $size = 0
): void
```

Pass `Barcode::CODE128` as the `$type`.

---

## Examples

### Basic Code 128

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::DOTS);
$builder->drawCode128(5, 50, 20, 'ABC123456789');
echo $builder->toZpl();
```

Output:

```
^XA
^FO5,50
^BCN,20,N,N,N,A
^FDABC123456789^FS
^XZ
```

### With human-readable text

```php
$builder->drawCode128(5, 50, 20, 'ABC123456789', true);
// Output: ^BCN,20,Y,N,N,A
```

### Rotated 90°

```php
use Zpl\Enums\Orientation;
$builder->drawCode128(5, 50, 20, 'ABC123', false, Orientation::ROTATED);
// Output: ^BCR,20,N,N,N,A
```

### Custom module width

```php
$builder->drawCode128(5, 50, 20, 'ABC123', true, Orientation::NORMAL, 3);
// Emits: ^BY3 before the barcode command
```

---

## Notes

- Code 128 automatically selects the most compact encoding (A, B, or C subsets).
- The library always uses mode `A` (automatic mode selection), which provides the best compression.
- Use `$size` (1–9) to scale the barcode width; larger values produce wider barcodes.
- See [All Barcode Types](barcodes.md) for a full list of supported symbologies.
