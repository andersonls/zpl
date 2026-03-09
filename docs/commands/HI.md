# `^HI` / `~HI` — Host Identification

> **ℹ️ Not yet implemented as a dedicated method.**
> Use `addPreCommand()` or send the raw command string to the printer.

## Overview

The `^HI` / `~HI` (Host Identification) command instructs the printer to return an **identification string** to the host. The string contains the printer model, firmware version, and configuration summary.

This command is useful for:
- Verifying printer connectivity.
- Detecting the firmware version in automated deployment scripts.
- Logging printer details during production monitoring.

---

## ZPL Syntax

```
^HI
~HI
```

No parameters. Both format-level (`^HI`) and device-level (`~HI`) variants are supported.

---

## Example Response

```
"ZPL II","V60.17.11Z","ZEBRA","ZT230","61J134900094"
```

Fields: firmware language, firmware version, manufacturer, model, serial number.

---

## PHP API

This command is not yet implemented as a dedicated method. Use one of these approaches:

```php
// Device command (no label wrapper needed)
use Zpl\Printer;
Printer::printer('192.168.1.100')->send('~HI');
// Response is returned over the same communications channel

// Inside a label format
$builder->addCommand('HI');
```

---

## Examples

### Query printer identity (device command)

```
~HI
```

### Inside a label format

```
^XA
^HI
^XZ
```

### Via PHP

```php
use Zpl\Printer;

// Send the identification query
// (reading the response requires raw socket handling)
Printer::printer('192.168.1.100')->send('~HI');
```

---

## Related Commands

| Command | Description |
|---------|-------------|
| `~HS` | Host Status Return — printer operational status (see [`HS.md`](HS.md)) |
| `^HH` | Print configuration label (see [`HH.md`](HH.md)) |
| `~WC` | Print configuration label (device command, see [`WC.md`](WC.md)) |

---

## Notes

- `~HI` is a **device command** and can be sent without a `^XA`/`^XZ` wrapper.
- The response is returned **to the host** over the active communications channel, not printed on a label.
- The library does not currently provide a method to send and receive `~HI`; you need raw socket I/O to read the response.
- The response format may vary between printer firmware versions.
