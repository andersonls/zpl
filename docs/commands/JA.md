# `~JA` — Cancel All

> **ℹ️ Not yet implemented as a dedicated method.**
> Use `addPreCommand()` or send the raw command string to the printer.

## Overview

The `~JA` (Cancel All) device command instructs the printer to **cancel all label formats** currently in the print buffer and any pending print jobs. The printer returns to an idle, ready state.

This command is used for emergency stop scenarios, clearing stuck jobs, and resetting the print queue.

---

## ZPL Syntax

```
~JA
```

No parameters. This is a **device command** — it can be sent without a `^XA`/`^XZ` wrapper.

---

## PHP API

This command is not yet implemented as a dedicated method. Use one of these approaches:

```php
// Send as a standalone device command
use Zpl\Printer;
Printer::printer('192.168.1.100')->send('~JA');

// Or add before the next label
$builder->addPreCommand('~JA');
```

---

## Examples

### Cancel all pending jobs

```
~JA
```

### Via PHP

```php
use Zpl\Printer;

Printer::printer('192.168.1.100')->send('~JA');
```

---

## Related Commands

| Command | Description |
|---------|-------------|
| `~JR` | Power-on reset (see [`JR.md`](JR.md)) |
| `~HS` | Query printer status (see [`HS.md`](HS.md)) |

---

## Notes

- `~JA` discards all buffered print jobs immediately; labels already being printed may be partially printed.
- This command is typically sent over a dedicated management connection, not the primary print connection.
- After `~JA`, the printer is ready to accept new label formats.
- Use with caution in production environments — cancelled labels are not automatically reprinted.
