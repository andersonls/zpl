# `^XF` — Recall Format

> **ℹ️ Not yet implemented as a dedicated method.**
> Use `addCommand()` or the dynamic call syntax to emit this command directly.

## Overview

The `^XF` (Recall Format) command retrieves a **previously stored label format** from the printer's flash memory or RAM and merges it into the current label. The stored format's fields are combined with any variable data (`^FN` fields) supplied in the current label.

This enables a powerful **forms-based printing** workflow:

1. Upload the label template once (using `^DF` or via the printer's storage tools).
2. At print time send only the variable data, referencing the stored format with `^XF`.

---

## ZPL Syntax

```
^XFd:o.x
```

| Parameter | Description | Example |
|-----------|-------------|---------|
| `d` | Storage device: `R` RAM, `E` flash (permanent), `B` flash (page) | `E` |
| `o` | Format filename (up to 8 characters) | `SHIPPING` |
| `x` | File extension (up to 3 characters) | `ZPL` |

---

## PHP API

This command is not yet implemented as a dedicated method. Use one of these approaches:

```php
// Recall format from flash memory
$builder->XF('E:SHIPPING.ZPL');
// Output: ^XFE:SHIPPING.ZPL

// Via addCommand()
$builder->addCommand('XF', 'E:LABEL.ZPL');
// Output: ^XFE:LABEL.ZPL
```

---

## Workflow Example

### Step 1 — Store the format on the printer (one-time setup)

```
^XA
^DFE:SHIPPING.ZPL^FS
^CF0,30
^FO10,10^FN1,"Recipient"^FH^FD^FS
^FO10,60^FN2,"Address"^FH^FD^FS
^FO10,120
^BCN,50,Y,N,N,A
^FN3,"Tracking"^FD^FS
^XZ
```

### Step 2 — Print with variable data (repeated at run time)

```
^XA
^XFE:SHIPPING.ZPL^FS
^FN1^FDJohn Smith^FS
^FN2^FD123 Main St, Springfield^FS
^FN3^FD1Z999AA10123456784^FS
^XZ
```

### Via PHP

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::DOTS);
$builder->addCommand('XF', 'E:SHIPPING.ZPL');
$builder->addCommand('FN', 1);
$builder->addCommand('FD', 'John Smith');
$builder->addCommand('FS');
$builder->addCommand('FN', 2);
$builder->addCommand('FD', '123 Main St');
$builder->addCommand('FS');
echo $builder->toZpl();
```

---

## Notes

- `^XF` must appear inside a `^XA`/`^XZ` block.
- The recalled format is merged with any commands in the current label; the recalled format's field coordinates and formatting take precedence.
- Storage device `E` (flash/permanent) is the most common for production use.
- See `^FN` (see [`FN.md`](FN.md)) for defining variable fields in stored formats.
- This workflow reduces network bandwidth — only variable data is transmitted per print cycle, not the full label format.
