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

The printer sends back **three fixed-width response packets**, each terminated with a carriage return and line feed (`\r\n`). Every position within a packet is a single ASCII character — `0` (condition false / not active) or `1` (condition true / active) unless noted otherwise.

---

### Packet 1 — Communications / Interface Status

| Position | Value | Description |
|----------|-------|-------------|
| 1 | `0`/`1` | Communications diagnostics mode — `1` = diagnostics mode is on |
| 2 | `0`/`1` | Partial format flag — `1` = a partial format is present in the receive buffer |
| 3 | `0` | Reserved (always `0`) |
| 4 | `0`/`1` | Corrupt RAM — `1` = RAM corruption detected |
| 5 | `0`/`1` | Under temperature — `1` = print head is below operating temperature |
| 6 | `0`/`1` | Over temperature — `1` = print head has exceeded maximum operating temperature |
| 7 | `0`/`1` | Power indicator — `1` = a power-on reset has occurred since the last `~HS` query |
| 8 | `0`/`1` | Label format data stored in printer RAM — `1` = at least one format is stored |
| 9 | `0`/`1` | Format data in receive buffer — `1` = format data waiting to be processed |
| 10 | `0` | Reserved (always `0`) |
| 11 | `0`/`1` | Password protection active — `1` = printer is password-protected |
| 12 | `0`/`1` | Scalable fonts installed — `1` = scalable (TrueType / OpenType) fonts are present |
| 13–14 | `00` | Reserved (always `00`) |

---

### Packet 2 — Miscellaneous Status

| Position | Value | Description |
|----------|-------|-------------|
| 1 | `0`/`1` | Maintenance mode active — `1` = calibration or head-clean cycle in progress |
| 2 | `0`/`1` | Slew relative — `1` = printer is currently slewing (advancing media) |
| 3 | `0`/`1` | Cutter installed — `1` = a cutter accessory is detected |
| 4 | `0`/`1` | Label waiting at peel position — `1` = a label is present at the peel/dispense point and has not been removed |
| 5 | `0`/`1` | Labels remaining in current batch — `0` = batch complete or no batch active; `1` = one or more labels remain to be printed |
| 6–14 | `000000000` | Reserved (always `0`) |

---

### Packet 3 — Print Status

| Position | Value | Description |
|----------|-------|-------------|
| 1 | `0`/`1` | Media out — `1` = no paper / labels detected by the media sensor |
| 2 | `0`/`1` | Ribbon out — `1` = ribbon is depleted, missing, or broken (thermal-transfer mode only) |
| 3 | `0`/`1` | Print head open — `1` = the print head is raised (not in the printing position) |
| 4 | `0`/`1` | Thermal transfer / direct thermal flag — `0` = thermal-transfer mode; `1` = direct-thermal mode |
| 5 | `0`/`1` | Print busy — `1` = the printer is currently printing a label |
| 6 | `0`/`1` | Print pause active — `1` = printing has been paused by the operator or a `^PP` command |
| 7 | `0`/`1` | Near-end of media (low paper warning) — `1` = the near-end sensor has been triggered |
| 8 | `0`/`1` | Label not taken (peel mode) — `1` = a label dispensed in peel/applicator mode has not been removed from the peel pad |
| 9–14 | `000000` | Reserved (always `0`) |

---

### Example Response

A printer that is idle, has media loaded, has ribbon loaded, and has the head closed will return:

```
000000000000  (Packet 1 — no errors)
000000000000  (Packet 2 — no maintenance, no cutter, no label waiting)
000000000000  (Packet 3 — media OK, ribbon OK, head closed, idle)
```

A printer with the print head open and no paper would return:

```
000000000000
000000000000
101000000000
```

(Packet 3, position 1 = media out; position 3 = head open.)

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
