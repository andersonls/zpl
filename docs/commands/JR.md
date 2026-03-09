# `~JR` — Power-on Reset

> **ℹ️ Not yet implemented as a dedicated method.**
> Use `addPreCommand()` or send the raw command string to the printer.

## Overview

The `~JR` (Power-on Reset) device command performs a **soft reset** of the printer — equivalent to powering the printer off and back on. All volatile settings are cleared and the printer reloads its stored configuration.

Use this command when:
- Recovering from a firmware error.
- Applying configuration changes that require a restart.
- Resetting the printer to a known state after misconfiguration.

---

## ZPL Syntax

```
~JR
```

No parameters. This is a **device command** — it can be sent without a `^XA`/`^XZ` wrapper.

> ⚠️ **Warning:** This command resets the printer immediately, discarding any buffered print jobs. Use with caution.

---

## PHP API

This command is not yet implemented as a dedicated method. Use one of these approaches:

```php
use Zpl\Printer;
Printer::printer('192.168.1.100')->send('~JR');
```

---

## Examples

### Soft reset the printer

```
~JR
```

### Via PHP

```php
use Zpl\Printer;

Printer::printer('192.168.1.100')->send('~JR');
```

---

## Related Commands

| Command | Description |
|---------|-------------|
| `~JA` | Cancel all buffered jobs (less disruptive, see [`JA.md`](JA.md)) |
| `~HS` | Query printer status (see [`HS.md`](HS.md)) |

---

## Notes

- After `~JR`, allow a few seconds for the printer to complete its restart sequence before sending new jobs.
- All volatile settings (such as those set by `^JM`, `~SD`, `^PR`, etc.) are reset to their saved values.
- Network-connected printers may briefly drop their TCP connection during the reset.
- For non-destructive job cancellation, use `~JA` (see [`JA.md`](JA.md)).
