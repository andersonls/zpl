# `~WC` — Print Configuration Label

> **ℹ️ Not yet implemented as a dedicated method.**
> Use `addPreCommand()` or send the raw command string to the printer.

## Overview

The `~WC` (Print Configuration Label) device command instructs the printer to **print a configuration label** showing its current hardware and software settings. It is the tilde-prefix (device command) equivalent of `^HH`.

The printed label typically includes:
- Firmware version and printer model
- Print darkness and print speed
- Media type, tracking method, and label size
- Network settings (IP address, MAC address)
- Serial port settings
- Memory and storage information

---

## ZPL Syntax

```
~WC
```

No parameters. This is a **device command** — it can be sent without a `^XA`/`^XZ` wrapper.

---

## PHP API

This command is not yet implemented as a dedicated method. Use one of these approaches:

```php
// Send as a standalone device command
use Zpl\Printer;
Printer::printer('192.168.1.100')->send('~WC');

// Or add it before the label
$builder->addPreCommand('~WC');
```

---

## Examples

### Print the configuration label

```
~WC
```

### Via PHP

```php
use Zpl\Printer;

Printer::printer('192.168.1.100')->send('~WC');
```

---

## Related Commands

| Command | Description |
|---------|-------------|
| `^HH` | Print configuration label (format command, see [`HH.md`](HH.md)) |
| `~HI` | Return identification string to host (see [`HI.md`](HI.md)) |
| `~HS` | Return host status packets (see [`HS.md`](HS.md)) |

---

## Notes

- `~WC` and `^HH` produce the same printed output; `~WC` is preferred for standalone use since it does not require a label format.
- This command causes the printer to consume one blank label for the configuration printout.
- Use during printer installation, troubleshooting, and routine maintenance to verify settings.
- The information printed by `~WC` matches what is accessible via `~HI` (returned as data) and the printer's onboard configuration menu.
