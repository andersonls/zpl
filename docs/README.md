# ZPL Command Reference

This folder contains documentation for all ZPL (Zebra Programming Language) commands supported by the `andersonls/zpl` PHP library.

Each command page documents the underlying ZPL syntax, the corresponding PHP API method, its parameters, and usage examples.

---

## Label Structure Commands

| Command | ZPL Code | Description |
|---------|----------|-------------|
| [Label Start / End](commands/XA-XZ.md) | `^XA` / `^XZ` | Begin and end a ZPL label |
| [Label Home](commands/LH.md) | `^LH` | Set the label origin (home position) |
| [Label Length](commands/LL.md) | `^LL` | Set the height of the label |
| [Print Width](commands/PW.md) | `^PW` | Set the width of the label |
| [Print Orientation](commands/PO.md) | `^PO` | Mirror the label contents |
| [Print Quantity](commands/PQ.md) | `^PQ` | Set the number of copies to print |

---

## Font & Text Commands

| Command | ZPL Code | Description |
|---------|----------|-------------|
| [Set Font](commands/CF.md) | `^CF` | Select font and size |
| [Character Encoding](commands/CI.md) | `^CI` | Set the character set / encoding |
| [Field Origin](commands/FO.md) | `^FO` | Position the cursor for the next field |
| [Field Write Direction](commands/FW.md) | `^FW` | Set text / field rotation |
| [Field Data / Separator](commands/FD-FS.md) | `^FD` / `^FS` | Write field data and close it |
| [Field Hexadecimal Indicator](commands/FH.md) | `^FH` | Enable hex escape sequences in field data |
| [Field Reverse](commands/FR.md) | `^FR` | Invert colors of the next field |
| [Field Block](commands/FB.md) | `^FB` | Constrain text to a block with wrapping and alignment |
| [Serial Number](commands/SN.md) | `^SN` | Auto-incrementing serial number field |

---

## Graphics & Drawing Commands

| Command | ZPL Code | Description |
|---------|----------|-------------|
| [Graphic Box](commands/GB.md) | `^GB` | Draw a rectangle or line |
| [Graphic Circle](commands/GC.md) | `^GC` | Draw a circle |
| [Graphic Field](commands/GF.md) | `^GF` | Embed a raster image |

---

## Barcode Commands

| Command | ZPL Code | Description |
|---------|----------|-------------|
| [Code 128](commands/BC.md) | `^BC` | Code 128 barcode |
| [Code 39](commands/B3.md) | `^B3` | Code 39 barcode |
| [QR Code](commands/BQ.md) | `^BQ` | QR Code 2D barcode |
| [Barcode Default / Width](commands/BY.md) | `^BY` | Set default barcode module width |
| [All Barcode Types](commands/barcodes.md) | `^B*` | Full reference of all 28 supported barcode symbologies |

---

## Printer Control Commands

| Command | ZPL Code | Description |
|---------|----------|-------------|
| [Set Darkness](commands/SD.md) | `~SD` | Adjust print darkness |

---

## Library-Specific Features

The library also supports:

- **Arbitrary commands** — call any ZPL command dynamically via `$builder->CF('A', 30)` (see each command page for details).
- **Pre / post commands** — use `addPreCommand()` / `addPostCommand()` to inject raw ZPL before `^XA` or after `^XZ`.
- **Macros** — register reusable label-building logic with `ZplBuilder::macro()`.
- **Multi-page labels** — use `newPage()` to include multiple labels in a single ZPL payload.
- **Unit conversion** — construct the builder with `Unit::MM` to work in millimetres; dots are computed automatically from the configured DPI (default 203).

---

## Quick Start

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::MM);
$builder->setFont('0', 16);
$builder->drawText(5, 5, 'Hello, World!');
echo $builder->toZpl();
```

Output:

```
^XA
^CF0,45
^FWN
^FO5,5
^FH^FDHello, World!^FS
^FWN
^XZ
```
