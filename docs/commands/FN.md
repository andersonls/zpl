# `^FN` — Field Number

> **ℹ️ Not yet implemented as a dedicated method.**
> Use `addCommand()` or the dynamic call syntax to emit this command directly.

## Overview

The `^FN` (Field Number) command assigns a numeric index to a field, turning it into a **variable data placeholder**. When a label format is stored on the printer (using `^XF` / `^DF`), host software can send only the variable data values at print time rather than retransmitting the entire label format.

This is the foundation of ZPL's **forms-based printing** workflow:

1. Download the label template once (`^XF` store).
2. At print time, send only the variable field values identified by `^FN` indices.

---

## ZPL Syntax

```
^FNn,"p"
```

| Parameter | Description | Default |
|-----------|-------------|---------|
| `n` | Field number (0–9999) | — |
| `"p"` | Optional field prompt string displayed in host queries | `""` |

---

## PHP API

This command is not yet implemented as a dedicated method. Use one of these approaches:

```php
// Dynamic method call
$builder->FN(1);
// Output: ^FN1

// With a prompt string
$builder->addCommand('FN', '1,"Product Name"');
// Output: ^FN1,"Product Name"
```

---

## Workflow Example

### Step 1 — Store the label format on the printer

```
^XA
^DFE:SHIPPING.ZPL^FS   ; store format as SHIPPING.ZPL
^CF0,30
^FO10,10
^FN1,"Name"            ; variable field 1: recipient name
^FH^FD^FS
^FO10,60
^FN2,"Address"         ; variable field 2: address
^FH^FD^FS
^FO10,110
^BC N,50,Y,N,N,A       ; fixed barcode (static)
^FD123456^FS
^XZ
```

### Step 2 — Print with variable data

```
^XA
^XFE:SHIPPING.ZPL^FS   ; recall format
^FN1^FDJohn Smith^FS   ; supply value for field 1
^FN2^FD123 Main St^FS  ; supply value for field 2
^XZ
```

### Via PHP (storing + recalling)

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

// Store the format
$template = new ZplBuilder(Unit::DOTS);
$template->addPreCommand('^DFE:LABEL.ZPL^FS');
$template->setFont('0', 30);
$template->addCommand('FO', 10, 10);
$template->addCommand('FN', 1);
$template->addCommand('FH');
$template->addCommand('FD'); // empty placeholder
$template->addCommand('FS');
// send $template->toZpl() once to the printer

// Print with data
$print = new ZplBuilder(Unit::DOTS);
$print->addCommand('XF', 'E:LABEL.ZPL');
$print->addCommand('FN', 1);
$print->addCommand('FD', 'Hello World');
$print->addCommand('FS');
echo $print->toZpl();
```

---

## Notes

- `^FN` indices must be unique within a format.
- The `"prompt"` string is optional and is only shown when the host queries the printer for field values.
- See [`^XF`](XF.md) for recalling stored formats.
- When not using stored formats, `^FN` can still be used to identify fields in a label and supply values via `^FN n ^FD data ^FS` inline.
