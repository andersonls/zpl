# `^MM` — Print Mode

> **ℹ️ Not yet implemented as a dedicated method.**
> Use `addCommand()` or the dynamic call syntax to emit this command directly.

## Overview

The `^MM` (Print Mode) command selects the **label dispensing mode** — how the printer advances and presents labels after printing. The available modes depend on the physical options installed on the printer (cutter, peeler, rewind unit, etc.).

---

## ZPL Syntax

```
^MMa,b
```

| Parameter | Description | Values | Default |
|-----------|-------------|--------|---------|
| `a` | Print mode | See table below | `T` |
| `b` | Pre-peel select (only relevant in peel mode) | `Y` / `N` | `N` |

### Print Mode Values

| Value | Mode | Description |
|-------|------|-------------|
| `T` | Tear-off | Standard mode; label advances to the tear bar |
| `P` | Peel-off | Liner is peeled away as each label is dispensed |
| `R` | Rewind | Label stock is rewound internally |
| `C` | Cutter | Label is cut after printing |
| `A` | Applicator | Used with label applicator hardware |
| `K` | Kiosk | Kiosk mode (specialty printers) |
| `S` | Linerless peel | Linerless media, peel mode |
| `D` | Linerless rewind | Linerless media, rewind mode |
| `F` | Linerless tear | Linerless media, tear-off mode |
| `G` | Linerless cut | Linerless media, cut mode |
| `N` | No motion | Label does not advance after print |

---

## PHP API

This command is not yet implemented as a dedicated method. Use one of these approaches:

```php
// Set cutter mode
$builder->MM('C');
// Output: ^MMC

// Via addCommand()
$builder->addCommand('MM', 'C');
// Output: ^MMC
```

---

## Examples

### Enable cutter mode

```
^XA
^MMC
^CF0,30
^FO10,10^FH^FDProduct Label^FS
^XZ
```

### Peel-off mode with pre-peel

```
^XA
^MMP,Y
^FO10,10^FH^FDPeel me!^FS
^XZ
```

### Via PHP

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::DOTS);
$builder->addCommand('MM', 'C');
$builder->drawText(10, 10, 'Product Label');
echo $builder->toZpl();
```

---

## Notes

- Not all modes are available on all printer models. Refer to your printer's hardware manual.
- `^MM` takes effect from the label in which it appears and persists until changed.
- Cutter mode (`C`) requires a cutter accessory; sending this to a printer without a cutter has no effect.
- For tear-off position adjustment, see [`^TA`](TA.md).
