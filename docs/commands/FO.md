# `^FO` — Field Origin

## Overview

The `^FO` (Field Origin) command sets the starting position (X, Y coordinates) for the next field on the label. All subsequent field content (`^FD`, `^GB`, `^GC`, barcodes, etc.) is rendered relative to this origin.

The library inserts `^FO` automatically before most drawing methods — you rarely need to call it directly.

---

## ZPL Syntax

```
^FOx,y,z
```

| Parameter | Description | Default |
|-----------|-------------|---------|
| `x` | Horizontal position in dots from the label home | `0` |
| `y` | Vertical position in dots from the label home | `0` |
| `z` | Justification (0 = left, 1 = right, 2 = auto) | `0` |

---

## PHP API

`^FO` is emitted internally by every drawing method (`drawText`, `drawRect`, `drawCircle`, `drawBarcode`, etc.). You do not normally call it directly.

To set the current cursor position for cell-based drawing, use:

```php
$builder->setXY(float $x, float $y): void
$builder->setX(float $x): void
$builder->setY(float $y): void
```

To emit a raw `^FO` command:

```php
$builder->addCommand('FO', $x, $y);
// or dynamically:
$builder->FO($x, $y);
```

---

## Examples

### How drawText emits ^FO automatically

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::DOTS);
$builder->drawText(50, 100, 'Hello');
echo $builder->toZpl();
```

Output:

```
^XA
^FWN
^FO50,100
^FH^FDHello^FS
^FWN
^XZ
```

### Emit ^FO directly

```php
$builder->FO(10, 20);
// Output: ^FO10,20
```

---

## Notes

- `^FO` must be placed immediately before the field content command.
- The library always converts coordinates to dots using the configured DPI and unit.
- When using `Unit::MM`, coordinates passed to drawing methods are automatically converted.
