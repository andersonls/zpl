# `^JZ` — Reprint After Error

> **ℹ️ Not yet implemented as a dedicated method.**
> Use `addCommand()` or the dynamic call syntax to emit this command directly.

## Overview

The `^JZ` (Reprint After Error) command controls whether the printer **automatically reprints** the label that was in progress when an error (such as paper-out or head-open) occurred and was subsequently cleared.

When enabled, the printer re-queues and reprints the interrupted label once the error condition is resolved.

---

## ZPL Syntax

```
^JZa
```

| Parameter | Description | Values | Default |
|-----------|-------------|--------|---------|
| `a` | Reprint behaviour | `Y` reprint on error recovery, `N` do not reprint | `Y` |

---

## PHP API

This command is not yet implemented as a dedicated method. Use one of these approaches:

```php
// Enable reprint after error
$builder->JZ('Y');
// Output: ^JZY

// Disable reprint
$builder->addCommand('JZ', 'N');
// Output: ^JZN
```

---

## Examples

### Enable reprint after error

```
^XA
^JZY
^CF0,30
^FO10,10^FH^FDProduct^FS
^XZ
```

### Disable reprint after error

```
^XA
^JZN
^FO10,10^FH^FDProduct^FS
^XZ
```

### Via PHP

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::DOTS);
$builder->addCommand('JZ', 'Y');
$builder->drawText(10, 10, 'Product');
echo $builder->toZpl();
```

---

## Notes

- When `^JZY` is set and a paper-out error occurs mid-batch, the interrupted label is reprinted once paper is reloaded.
- When `^JZN` is set, the interrupted label is **not** reprinted; the batch continues from the next label.
- This setting persists until changed or the printer is reset.
- Useful in unattended printing environments where label completeness is critical.
