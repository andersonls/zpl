# `^RO` — Reset Label Counter

> **ℹ️ Not yet implemented as a dedicated method.**
> Use `addCommand()` or the dynamic call syntax to emit this command directly.

## Overview

The `^RO` (Reset Label Counter) command resets the printer's **internal label counter** to zero. The label counter is incremented each time a label is printed and is accessible via the host status response (`~HS`). It is used for tracking print volume and label reconciliation.

---

## ZPL Syntax

```
^ROt
```

| Parameter | Description | Values | Default |
|-----------|-------------|--------|---------|
| `t` | Counter type to reset | `L` label counter, `F` font counter, `A` all counters | `A` |

> **Note:** The `L` (label) counter and the `A` (all) reset are supported on all current Zebra printers. The `F` (font) counter is only available on printers that maintain a separate downloadable-font usage counter; on printers without this counter, sending `^ROF` behaves identically to `^ROA`.

---

## PHP API

This command is not yet implemented as a dedicated method. Use one of these approaches:

```php
// Reset all counters
$builder->RO('A');
// Output: ^ROA

// Via addCommand()
$builder->addCommand('RO', 'L');
// Output: ^ROL
```

---

## Examples

### Reset all counters

```
^XA
^ROA
^XZ
```

### Reset label counter only

```
^XA
^ROL
^XZ
```

### Via PHP

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::DOTS);
$builder->addCommand('RO', 'A');
echo $builder->toZpl();
```

---

## Notes

- `^RO` is typically used during printer initialisation or at the start of a production run.
- The current counter value can be retrieved via `~HS` (see [`HS.md`](HS.md)).
- Not all printer models expose a separate font counter; use `A` for broadest compatibility.
