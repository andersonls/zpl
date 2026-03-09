# `^HS` / `~HS` — Host Status Return

> **ℹ️ Not yet implemented as a dedicated method.**
> Use `addPreCommand()` or send the raw command string to the printer.

## Overview

The `^HS` / `~HS` (Host Status Return) command instructs the printer to send a **status response string** back to the host over the active communications port. The response provides real-time information about the printer's current state.

This command is essential for host-to-printer handshaking and monitoring in production environments.

---

## ZPL Syntax

```
^HSa
~HS
```

Both `^HS` (format command, inside `^XA`/`^XZ`) and `~HS` (device command, outside any label) are supported.

| Parameter | Description | Values | Default |
|-----------|-------------|--------|---------|
| `a` | Return type | Omit for all status (three packets) | All packets |

---

## Response Format

The printer sends back **three fixed-width packets** (each 14 bytes plus terminator), containing:

### Packet 1 — Communications / Interface Status

| Byte | Description |
|------|-------------|
| 1 | Label length (in dots) |
| 2 | Number of formats in receive buffer |
| 3 | Number of graphics stored |
| 4 | Password |
| 5 | Scalable font status |

### Packet 2 — Miscellaneous Status

| Byte | Description |
|------|-------------|
| 1 | Number of formats currently in buffer |
| 2 | Number of labels remaining in current batch |
| 3 | Always `0` |
| 4 | Always `0` |
| 5 | Always `0` |

### Packet 3 — Print Status

| Byte | Description |
|------|-------------|
| 1–8 | Paper/ribbon status bits |

> **Note:** Refer to the Zebra ZPL II Programming Guide (Section "^HS") for the complete bit-field layout of all three packets.

---

## PHP API

This command is not yet implemented as a dedicated method. Use one of these approaches:

```php
// Send as a device command (outside ^XA/^XZ)
$builder->addPreCommand('~HS');

// Or send as a format command
$builder->addCommand('HS');
```

To send `~HS` directly without a label:

```php
use Zpl\Printer;

Printer::printer('192.168.1.100')->send('~HS');
```

---

## Examples

### Query printer status

```
~HS
```

No `^XA`/`^XZ` needed for `~HS` — it is a device-level command.

### Inside a label format

```
^XA
^HS
^CF0,30
^FO10,10^FH^FDProduct^FS
^XZ
```

### Via PHP — send status request and handle response

```php
use Zpl\Printer;

$socket = Printer::printer('192.168.1.100');
$socket->send('~HS');
// Read the 3-packet response from the socket
// (reading the response requires raw socket handling)
```

---

## Notes

- `~HS` is a **device command** and can be sent outside a label format.
- `^HS` inside a label format triggers the status response when that label is processed.
- The response is sent over the **same communications channel** (TCP, serial, USB) that the command arrived on.
- The library does not currently provide a method to receive and parse the status response; this requires direct socket I/O.
- Typical uses: poll the printer before sending a batch job, detect paper-out / ribbon-out conditions, verify the printer is ready.
