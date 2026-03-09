# `^PR` — Print Rate (Speed)

> **ℹ️ Not yet implemented as a dedicated method.**
> Use `addCommand()` or the dynamic call syntax to emit this command directly.

## Overview

The `^PR` (Print Rate) command sets the **print speed** and optionally the **slew speed** and **back-feed speed** of the printer. Lower speeds generally improve print quality; higher speeds increase throughput.

---

## ZPL Syntax

```
^PRp,s,b
```

| Parameter | Description | Range | Default |
|-----------|-------------|-------|---------|
| `p` | Print speed (inches per second) | 2–14 (printer-dependent) | `2` |
| `s` | Slew speed (label advance speed) | 2–14 | Same as `p` |
| `b` | Back-feed speed | 2–14 | Same as `p` |

Speed values represent **inches per second (ips)**. Valid values depend on the printer's maximum rated speed. If a value exceeds the printer's capability, the firmware clamps it to the maximum supported speed.

### Supported speeds by printer class

| Printer class | Max print speed |
|---------------|-----------------|
| Entry-level (e.g., ZD220, ZD420) | 6 ips |
| Mid-range (e.g., ZD620, ZT230, ZT410) | 8–10 ips |
| Industrial (e.g., ZT510, ZT610, ZT620) | 12–14 ips |
| Desktop thermal-transfer (e.g., ZD500) | 4–6 ips |

Common accepted speed values across most models: `2`, `3`, `4`, `5`, `6`, `8`, `10`, `12`, `14`.

---

## PHP API

This command is not yet implemented as a dedicated method. Use one of these approaches:

```php
// Set print speed to 4 ips
$builder->PR(4);
// Output: ^PR4

// Set print and slew speeds separately
$builder->addCommand('PR', 4, 6);
// Output: ^PR4,6
```

---

## Examples

### Slow, high-quality print

```
^XA
^PR2
^CF0,30
^FO10,10^FH^FDHigh quality label^FS
^XZ
```

### Fast throughput

```
^XA
^PR8
^FO10,10^FH^FDFast label^FS
^XZ
```

### Set print and slew speeds

```
^XA
^PR4,8
^FO10,10^FH^FDProduct^FS
^XZ
```

### Via PHP

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::DOTS);
$builder->addCommand('PR', 4);
$builder->drawText(10, 10, 'High quality label');
echo $builder->toZpl();
```

---

## Notes

- Higher speeds can reduce print quality, particularly for fine barcodes and small text.
- Lower speeds (2–4 ips) are recommended for high-resolution (300–600 DPI) printers.
- The valid speed range varies by printer model; unsupported values are typically clamped to the nearest valid speed.
- `^PR` is a persistent setting until the printer is reconfigured.
- For DPI configuration in the library, see `setDpi()` / `setDpmm()` in the PHP API.
