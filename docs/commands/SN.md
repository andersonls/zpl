# `^SN` — Serial Number

## Overview

The `^SN` (Serial Number) command prints an auto-incrementing number field. When the printer receives a `^PQ` quantity greater than 1, each copy of the label has the serial number incremented by the configured step. This is useful for printing numbered labels, ticket numbers, or sequential IDs without sending separate ZPL jobs.

---

## ZPL Syntax

```
^SNs,i,p
```

| Parameter | Description | Default |
|-----------|-------------|---------|
| `s` | Starting value (may have leading zeros, e.g. `001`) | — |
| `i` | Increment (step) | `1` |
| `p` | Pad with leading zeros to match starting value length | `Y` |

---

## PHP API

```php
$builder->drawSerialNumber(
    float $x,
    float $y,
    string $start = '1',
    int $step = 1,
    bool $pad = true,
    bool $invert = false
): void
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$x` | `float` | X position in the current unit. |
| `$y` | `float` | Y position in the current unit. |
| `$start` | `string` | Starting serial number. Use leading zeros to set the minimum field width (e.g. `'001'`). |
| `$step` | `int` | Increment per label. Values ≤ 0 are treated as `1`. |
| `$pad` | `bool` | Pad the number with leading zeros to match the length of `$start`. |
| `$invert` | `bool` | Invert colors (adds `^FR`). |

---

## Examples

### Simple counter starting at 1

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::DOTS);
$builder->drawSerialNumber(10, 50, '1');
$builder->setQuantity(5);
echo $builder->toZpl();
```

Output:

```
^XA
^FO10,50
^SN1,1,Y^FS
^PQ5,0,0
^XZ
```

The printer will print 5 labels numbered 1, 2, 3, 4, 5.

### Zero-padded counter (e.g. 001, 002, …)

```php
$builder->drawSerialNumber(10, 50, '001');
// Output: ^SN001,1,Y^FS  → prints 001, 002, 003, ...
```

### Custom step

```php
$builder->drawSerialNumber(10, 50, '10', 5);
// Output: ^SN10,5,Y^FS  → prints 10, 15, 20, 25, ...
```

### Without zero padding

```php
$builder->drawSerialNumber(10, 50, '001', 1, false);
// Output: ^SN001,1,N^FS  → prints 1, 2, 3, ... (no padding)
```

### Inverted serial number

```php
$builder->drawSerialNumber(10, 50, '1', 1, true, true);
// Output: ^FO10,50\n^FR\n^SN1,1,Y^FS
```

---

## Notes

- The serial number is rendered using the current font (`^CF`) setting.
- The font must be set before `drawSerialNumber()` for the correct size to apply.
- `$step` is always at least `1`; negative or zero values are clamped.
- Set the print quantity with `setQuantity()` to control how many incremented copies are printed.
