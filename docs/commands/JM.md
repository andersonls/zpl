# `^JM` — Set Dots Per Millimeter

> **ℹ️ Not yet implemented as a dedicated method.**
> Use `addCommand()` or the dynamic call syntax to emit this command directly.

## Overview

The `^JM` (Set Dots Per Millimeter) command configures the **print density** of the printer at the ZPL level. All subsequent dot-count calculations in the label format are adjusted accordingly.

> **Note:** The PHP library provides `setDpi()` and `setDpmm()` methods that control the same conversion factor **in the library**, but they do not emit `^JM`. Use `^JM` when you need to set the printer's density directly via ZPL.

---

## ZPL Syntax

```
^JMa
```

| Parameter | Description | Values |
|-----------|-------------|--------|
| `a` | Dots per millimeter | `A` 6 dpmm (152 DPI), `B` 8 dpmm (203 DPI), `C` 12 dpmm (300 DPI), `D` 24 dpmm (600 DPI) |

| Value | Dots/mm | DPI equivalent |
|-------|---------|---------------|
| `A` | 6 | 152 |
| `B` | 8 | 203 |
| `C` | 12 | 300 |
| `D` | 24 | 600 |

---

## PHP API

This command is not yet implemented as a dedicated method. Use one of these approaches:

```php
// Set 203 DPI (8 dpmm) via raw command
$builder->JM('B');
// Output: ^JMB

// Via addCommand()
$builder->addCommand('JM', 'C'); // 300 DPI
// Output: ^JMC
```

The PHP library's DPI configuration (does **not** emit `^JM`):

```php
$builder->setDpi(300);    // sets the PHP unit-conversion factor only
$builder->setDpmm(12);    // equivalent: 12 dpmm = 300 DPI
```

---

## Examples

### Set 300 DPI printer density

```
^XA
^JMC
^CF0,30
^FO10,10^FH^FDHigh resolution label^FS
^XZ
```

### Via PHP

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::MM, 300); // library unit conversion at 300 DPI
$builder->addCommand('JM', 'C');          // also tell the printer firmware
$builder->drawText(5, 5, 'Hello');
echo $builder->toZpl();
```

---

## Notes

- `^JM` changes are **persistent** on the printer; they affect all subsequent label formats until reset.
- Always match the `^JM` value to the printer's physical print head resolution to ensure correct sizing.
- The library's `setDpi()` / `setDpmm()` methods adjust how PHP calculates dot counts but do not tell the printer its own resolution — use both if needed.
- Common printers: Zebra ZD220 / ZD420 = 203 DPI (`B`); ZD620 = 300 DPI (`C`); ZT610 = up to 600 DPI (`D`).
