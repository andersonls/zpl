# `^FW` — Field Write Direction (Orientation)

## Overview

The `^FW` (Field Write Direction) command sets the default rotation for all subsequent fields on the label. It controls the direction in which text and barcodes are printed.

---

## ZPL Syntax

```
^FWr
```

| Parameter | Description |
|-----------|-------------|
| `r` | Rotation: `N` normal, `R` rotated 90°, `I` inverted 180°, `B` bottom-up 270° |

---

## PHP API

```php
$builder->setOrientation(Orientation $orientation = Orientation::NORMAL): void
```

### Orientation Enum

```php
use Zpl\Enums\Orientation;

Orientation::NORMAL    // 'N' — 0°, standard left-to-right
Orientation::ROTATED   // 'R' — 90° clockwise
Orientation::INVERTED  // 'I' — 180°, upside down
Orientation::BOTTOM_UP // 'B' — 270° clockwise (bottom-up)
```

The library also resets orientation to `^FWN` (normal) after every `drawText()` call to prevent orientation from leaking into subsequent fields.

---

## Examples

### Normal (default)

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;
use Zpl\Enums\Orientation;

$builder = new ZplBuilder(Unit::DOTS);
$builder->drawText(50, 50, 'Normal text');
echo $builder->toZpl();
```

Output:

```
^XA
^FWN
^FO50,50
^FH^FDNormal text^FS
^FWN
^XZ
```

### Rotated 90°

```php
$builder->drawText(50, 50, 'Rotated', Orientation::ROTATED);
```

Output:

```
^FWR
^FO50,50
^FH^FDRotated^FS
^FWN
```

### Inverted 180°

```php
$builder->drawText(50, 50, 'Upside Down', Orientation::INVERTED);
// Emits ^FWI before the field
```

### Set orientation explicitly

```php
$builder->setOrientation(Orientation::BOTTOM_UP);
// Output: ^FWB
```

---

## Notes

- `drawText()` accepts an `Orientation` parameter and emits `^FW<value>` before the `^FO` command.
- After every `drawText()` call, the library automatically resets orientation to `^FWN` to avoid side effects on subsequent fields.
- `drawBarcode()` and its helpers also accept an `Orientation` parameter.
