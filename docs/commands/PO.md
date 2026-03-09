# `^PO` — Print Orientation

## Overview

The `^PO` (Print Orientation) command mirrors the entire label content across a horizontal axis, effectively inverting the label top-to-bottom. This is useful when labels need to print upside-down for a specific application or mounting orientation.

If this command appears more than once in a label, the **last** occurrence takes precedence.

---

## ZPL Syntax

```
^POa
```

| Parameter | Description |
|-----------|-------------|
| `a` | `N` — normal orientation (default), `I` — inverted (mirrored) |

---

## PHP API

```php
$builder->invertLabelOrientation(bool $isInvert = true): void
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$isInvert` | `bool` | `true` to invert (`^POI`), `false` to reset to normal (`^PON`). |

---

## Examples

### Invert the label

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::MM);
$builder->invertLabelOrientation(true);
$builder->drawText(5, 5, 'Inverted Label');
echo $builder->toZpl();
```

Output:

```
^XA
^POI
^FWN
^FO5,5
^FH^FDInverted Label^FS
^FWN
^XZ
```

### Reset to normal orientation

```php
$builder->invertLabelOrientation(false);
// Output: ^PON
```

---

## Notes

- `^PO` affects the **entire label**, not individual fields (use `^FR` for per-field inversion).
- If called multiple times, only the last call takes effect.
- `^PO` does not rotate the label (use `^FW` / `Orientation` for per-field rotation).
- The default state when `^PO` is absent is normal orientation (`N`).
