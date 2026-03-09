# `^SF` — Serialization Field

> **ℹ️ Not yet implemented as a dedicated method.**
> Use `addCommand()` or the dynamic call syntax to emit this command directly.

## Overview

The `^SF` (Serialization Field) command marks the current field as a **serialized variable field**. When combined with a print quantity (`^PQ`), the field data is incremented (or decremented) by a specified step for each copy printed.

`^SF` is similar to `^SN` (Serial Number) but is more flexible — it can serialize any field type, including barcodes.

---

## ZPL Syntax

```
^SFa,b
```

| Parameter | Description | Default |
|-----------|-------------|---------|
| `a` | Start character position (1-based index within `^FD` data to start incrementing) | `1` |
| `b` | Increment step | `1` |

---

## PHP API

This command is not yet implemented as a dedicated method. Use one of these approaches:

```php
// Mark a field as serialized
$builder->addCommand('FO', 10, 10);
$builder->addCommand('FD', 'A001');
$builder->SF(1, 1);
$builder->addCommand('FS');
// Output: ^FO10,10^FDA001^SF1,1^FS
```

---

## Examples

### Serialize a text field

```
^XA
^CF0,30
^FO10,10
^FH^FDA001
^SF1,1
^FS
^PQ5,0,0
^XZ
```

Prints 5 labels with values: `A001`, `A002`, `A003`, `A004`, `A005`.

### Serialize a barcode field

```
^XA
^FO10,60
^BCN,50,Y,N,N,A
^FD00100
^SF3,1
^FS
^PQ3,0,0
^XZ
```

Prints 3 labels with barcodes: `00100`, `00101`, `00102`.

### Via PHP

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::DOTS);
$builder->setFont('0', 30);
$builder->addCommand('FO', 10, 10);
$builder->addCommand('FH');
$builder->addCommand('FD', 'A001');
$builder->addCommand('SF', 1, 1);
$builder->addCommand('FS');
$builder->setQuantity(5);
echo $builder->toZpl();
```

---

## `^SF` vs `^SN`

| Feature | `^SF` | `^SN` |
|---------|-------|-------|
| Applies to | Any field type (text, barcode) | Text / display fields only |
| Start position | Configurable character index | Always starts from the beginning |
| Dedicated PHP method | Not yet | Yes — `drawSerialNumber()` |

---

## Notes

- `^SF` must appear immediately after `^FD` and before `^FS`.
- The `a` (start position) is 1-based and refers to the character position within the `^FD` data where incrementing begins.
- For simple sequential numbering of text fields, use the library's `drawSerialNumber()` method (see [`SN.md`](../commands/SN.md)).
