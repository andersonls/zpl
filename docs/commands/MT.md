# `^MT` — Media Type

> **ℹ️ Not yet implemented as a dedicated method.**
> Use `addCommand()` or the dynamic call syntax to emit this command directly.

## Overview

The `^MT` (Media Type) command tells the printer which type of thermal media is loaded. This affects the print engine settings — direct thermal media is printed by heat alone, while thermal transfer media requires a ribbon.

---

## ZPL Syntax

```
^MTt
```

| Parameter | Description | Values |
|-----------|-------------|--------|
| `t` | Media type | `T` thermal transfer, `D` direct thermal |

---

## PHP API

This command is not yet implemented as a dedicated method. Use one of these approaches:

```php
// Direct thermal (no ribbon)
$builder->MT('D');
// Output: ^MTD

// Thermal transfer (with ribbon)
$builder->addCommand('MT', 'T');
// Output: ^MTT
```

---

## Examples

### Direct thermal media

```
^XA
^MTD
^CF0,30
^FO10,10^FH^FDDirect thermal label^FS
^XZ
```

### Thermal transfer media

```
^XA
^MTT
^FO10,10^FH^FDTransfer label^FS
^XZ
```

### Via PHP

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::DOTS);
$builder->addCommand('MT', 'D');
$builder->drawText(10, 10, 'Direct thermal');
echo $builder->toZpl();
```

---

## Notes

- `^MT` is a persistent printer setting; once set, it remains until changed or the printer is reconfigured.
- Setting the wrong media type can result in blank labels (sending thermal-transfer commands to a direct-thermal printer, or vice versa).
- Some Zebra printers (for example, ZD420, ZD620, ZT230, ZT410) support automatic media sensing: the printer detects ribbon presence at power-on and configures itself accordingly. On these models, sending `^MT` overrides the auto-detected value.
- Direct thermal media `D` does not require a ribbon, making it lower cost for short-lived labels (shipping, receipts).
- Thermal transfer media `T` with a ribbon produces more durable prints suited for long-lasting labels.
