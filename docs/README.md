# ZPL Command Reference

This folder contains documentation for ZPL (Zebra Programming Language) commands — both those **implemented** in the `andersonls/zpl` PHP library and those that exist in the ZPL specification but are **not yet implemented**.

Each command page documents the underlying ZPL syntax, the corresponding PHP API method (if available), parameters, and usage examples. Pages for unimplemented commands show how to emit them via `addCommand()` or the dynamic call interface.

---

## Implemented Commands

### Label Structure

| Command | ZPL Code | Description |
|---------|----------|-------------|
| [Label Start / End](commands/XA-XZ.md) | `^XA` / `^XZ` | Begin and end a ZPL label |
| [Label Home](commands/LH.md) | `^LH` | Set the label origin (home position) |
| [Label Length](commands/LL.md) | `^LL` | Set the height of the label |
| [Print Width](commands/PW.md) | `^PW` | Set the width of the label |
| [Print Orientation](commands/PO.md) | `^PO` | Mirror the label contents |
| [Print Quantity](commands/PQ.md) | `^PQ` | Set the number of copies to print |

### Font & Text

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

### Graphics & Drawing

| Command | ZPL Code | Description |
|---------|----------|-------------|
| [Graphic Box](commands/GB.md) | `^GB` | Draw a rectangle or line |
| [Graphic Circle](commands/GC.md) | `^GC` | Draw a circle |
| [Graphic Field](commands/GF.md) | `^GF` | Embed a raster image |

### Barcodes

| Command | ZPL Code | Description |
|---------|----------|-------------|
| [Code 128](commands/BC.md) | `^BC` | Code 128 barcode |
| [Code 39](commands/B3.md) | `^B3` | Code 39 barcode |
| [QR Code](commands/BQ.md) | `^BQ` | QR Code 2D barcode |
| [Barcode Default / Width](commands/BY.md) | `^BY` | Set default barcode module width |
| [All Barcode Types](commands/barcodes.md) | `^B*` | Full reference of all 28 supported barcode symbologies |

### Printer Control

| Command | ZPL Code | Description |
|---------|----------|-------------|
| [Set Darkness](commands/SD.md) | `~SD` | Adjust print darkness |

---

## Not Yet Implemented Commands

The commands below exist in the ZPL II specification and can be used with this library via `addCommand()`, `addPreCommand()`, or the dynamic call interface (e.g. `$builder->HS()`). A dedicated PHP method does not yet exist for them.

### Font & Text

| Command | ZPL Code | Description |
|---------|----------|-------------|
| [Use Font](commands/A.md) | `^A` / `^A@` | Per-field font selection (built-in or downloaded font) |
| [Field Typeset](commands/FT.md) | `^FT` | Position a field by its text baseline |
| [Field Number](commands/FN.md) | `^FN` | Variable-data placeholder for stored label formats |
| [Comment Field](commands/FX.md) | `^FX` | Non-printing comment / annotation |
| [Text Block](commands/TB.md) | `^TB` | Wrapping text block with rotation support |
| [Change Caret / Change Tilde](commands/CC-CT.md) | `^CC` / `^CT` | Replace the `^` and `~` prefix characters |

### Graphics & Drawing

| Command | ZPL Code | Description |
|---------|----------|-------------|
| [Graphic Diagonal Line](commands/GD.md) | `^GD` | Draw a diagonal line |
| [Graphic Ellipse](commands/GE.md) | `^GE` | Draw an ellipse / oval |

### Media & Print Configuration

| Command | ZPL Code | Description |
|---------|----------|-------------|
| [Media Darkness](commands/MD.md) | `^MD` | Adjust print darkness relative to the current setting |
| [Print Mode](commands/MM.md) | `^MM` | Select label dispensing mode (tear, cut, peel, rewind) |
| [Media Tracking](commands/MN.md) | `^MN` | Configure label gap / mark sensing |
| [Media Type](commands/MT.md) | `^MT` | Set direct thermal or thermal transfer media |
| [Print Rate](commands/PR.md) | `^PR` | Set print speed |
| [Tear-off Adjust](commands/TA.md) | `^TA` | Adjust the tear-off position |
| [Set Dots Per Millimeter](commands/JM.md) | `^JM` | Configure print density at the firmware level |

### Host & Status Commands

| Command | ZPL Code | Description |
|---------|----------|-------------|
| [Host Status Return](commands/HS.md) | `^HS` / `~HS` | Request printer status packets from the printer |
| [Host Configuration Label](commands/HH.md) | `^HH` | Print the printer configuration label |
| [Host Identification](commands/HI.md) | `^HI` / `~HI` | Return printer model / firmware identification string |
| [Print Configuration Label](commands/WC.md) | `~WC` | Print configuration label (device command) |

### Label & Print Control

| Command | ZPL Code | Description |
|---------|----------|-------------|
| [Reprint After Error](commands/JZ.md) | `^JZ` | Control reprint of interrupted labels |
| [Reset Label Counter](commands/RO.md) | `^RO` | Reset the printer's internal label counter |
| [Cancel All](commands/JA.md) | `~JA` | Cancel all buffered print jobs |
| [Power-on Reset](commands/JR.md) | `~JR` | Perform a soft reset of the printer |

### Variable & Stored Data

| Command | ZPL Code | Description |
|---------|----------|-------------|
| [Serialization Field](commands/SF.md) | `^SF` | Auto-increment any field type across copies |
| [Recall Format](commands/XF.md) | `^XF` | Recall a stored label format from printer memory |
| [Set Date and Time](commands/ST.md) | `^ST` | Set the printer's real-time clock |

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
