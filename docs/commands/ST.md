# `^ST` — Set Date and Time

> **ℹ️ Not yet implemented as a dedicated method.**
> Use `addCommand()` or the dynamic call syntax to emit this command directly.

## Overview

The `^ST` (Set Date and Time) command sets the printer's **internal real-time clock (RTC)**. Once set, the printer clock can be used in label formats via the `^FC` field clock command to print timestamps, expiry dates, and other time-based data automatically.

---

## ZPL Syntax

```
^STd,t
```

| Parameter | Description | Format |
|-----------|-------------|--------|
| `d` | Date | `MM/DD/YY` or `MM/DD/YYYY` |
| `t` | Time | `HH:MM:SS` (24-hour) |

---

## PHP API

This command is not yet implemented as a dedicated method. Use one of these approaches:

```php
// Set the printer clock to a specific date/time
$builder->ST('03/15/25', '14:30:00');
// Output: ^ST03/15/25,14:30:00

// Using the current system time
$now = new \DateTime();
$builder->addCommand('ST', $now->format('m/d/y'), $now->format('H:i:s'));
// Output: ^ST03/15/25,14:30:00
```

---

## Examples

### Set date and time

```
^XA
^ST03/15/25,14:30:00
^XZ
```

### Print a timestamp using the clock

```
^XA
^ST03/15/25,14:30:00
^CF0,30
^FO10,10
^FH^FDPrinted: ^FS
^FO120,10
^FCMM/DD/YY,HH:MM:SS     ; ^FC — field clock (displays current printer time)
^FD^FS
^XZ
```

### Via PHP

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::DOTS);
$now = new \DateTime();
$builder->addCommand('ST', $now->format('m/d/y'), $now->format('H:i:s'));
echo $builder->toZpl();
```

---

## Notes

- `^ST` requires a printer with a built-in real-time clock (RTC). Not all Zebra printer models include an RTC.
- Once set, the clock continues to run while the printer is powered; it may reset on power loss if the printer lacks a battery-backed RTC.
- The companion command `^FC` (Field Clock) reads the RTC value and prints it in a user-defined format — it is not currently implemented in the library either.
- Synchronizing the printer clock periodically (e.g. at the start of each shift) ensures accurate timestamps on labels.
