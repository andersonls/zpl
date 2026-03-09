# `^HH` — Return Host Configuration Label

> **ℹ️ Not yet implemented as a dedicated method.**
> Use `addPreCommand()` or send the raw command string to the printer.

## Overview

The `^HH` (Host Configuration Label) command instructs the printer to print a **configuration label** that shows its current settings — media type, print speed, darkness, DPI, firmware version, network settings, and more.

This is useful for initial printer setup, diagnostics, and auditing printer configurations in a fleet.

---

## ZPL Syntax

```
^HH
```

No parameters.

---

## PHP API

This command is not yet implemented as a dedicated method. Use one of these approaches:

```php
// Print the configuration label (outside any label format)
$builder->addPreCommand('^HH');

// Or send directly via Printer
use Zpl\Printer;
Printer::printer('192.168.1.100')->send('^XA^HH^XZ');
```

---

## Examples

### Print configuration label

```
^XA
^HH
^XZ
```

### Via PHP

```php
use Zpl\Printer;

Printer::printer('192.168.1.100')->send('^XA^HH^XZ');
```

---

## Related Commands

| Command | Description |
|---------|-------------|
| `~WC` | Print configuration label (device command, see [`WC.md`](WC.md)) |
| `~HI` | Return printer identification string (see [`HI.md`](HI.md)) |
| `~HS` | Return host status packets (see [`HS.md`](HS.md)) |

---

## Notes

- `^HH` triggers a physical printout on the printer — it does not return data to the host.
- To receive printer information as a data string (rather than printing it), use `~HI` (see [`HI.md`](HI.md)).
- `~WC` is the device-command equivalent; both produce the same configuration label printout.
- Useful during printer deployment and periodic maintenance checks.
