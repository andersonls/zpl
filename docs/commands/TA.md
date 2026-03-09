# `^TA` — Set Tear-off Adjust Position

> **ℹ️ Not yet implemented as a dedicated method.**
> Use `addCommand()` or the dynamic call syntax to emit this command directly.

## Overview

The `^TA` (Tear-off Adjust) command fine-tunes the position where the label stops after printing, relative to the tear bar. A positive value moves the label further out (past the tear bar); a negative value retracts it.

This is useful when labels consistently tear at the wrong position or when alignment with a cutter/peeler is off.

---

## ZPL Syntax

```
^TAn
```

| Parameter | Description | Range | Default |
|-----------|-------------|-------|---------|
| `n` | Offset in dots | –120 to +120 | `0` |

Positive values advance the label forward (more label exposed); negative values retract it.

---

## PHP API

This command is not yet implemented as a dedicated method. Use one of these approaches:

```php
// Advance label 20 dots past tear bar
$builder->TA(20);
// Output: ^TA20

// Retract label 10 dots
$builder->addCommand('TA', -10);
// Output: ^TA-10
```

---

## Examples

### Advance label 20 dots

```
^XA
^TA20
^CF0,30
^FO10,10^FH^FDProduct^FS
^XZ
```

### Retract label 10 dots

```
^XA
^TA-10
^FO10,10^FH^FDProduct^FS
^XZ
```

### Via PHP

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::DOTS);
$builder->addCommand('TA', 20);
$builder->drawText(10, 10, 'Product');
echo $builder->toZpl();
```

---

## Notes

- `^TA` affects **all subsequent labels** until changed or reset.
- The unit is **dots**; at 203 DPI, 20 dots ≈ 2.5 mm.
- This command is typically used during printer setup and calibration, not on every label.
- For print mode selection (tear off, peel, cut), see [`^MM`](MM.md).
