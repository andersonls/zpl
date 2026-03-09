# Barcode Types Reference

## Overview

This library supports **28 barcode symbologies** via the `Zpl\Enums\Barcode` enum. All barcode types can be used with the generic `drawBarcode()` method. The most common types (`Code 128`, `Code 39`, and `QR Code`) also have dedicated convenience methods.

---

## Supported Symbologies

| Enum Case | ZPL Command | Symbology | Description |
|-----------|-------------|-----------|-------------|
| `Barcode::AZTEK` | `^B0` | Aztec Code | 2D matrix barcode for transport ticketing |
| `Barcode::CODE11` | `^B1` | Code 11 | Numeric-only, used in telecom equipment |
| `Barcode::INTERLEAVED2` | `^B2` | Interleaved 2 of 5 | Compact numeric barcode for cartons |
| `Barcode::CODE39` | `^B3` | Code 39 | Alphanumeric (A–Z, 0–9 and symbols); see [`^B3`](B3.md) |
| `Barcode::CODE49` | `^B4` | Code 49 | Stacked linear barcode for dense data |
| `Barcode::PLANET` | `^B5` | PLANET | USPS mail tracking barcode |
| `Barcode::PDF417` | `^B7` | PDF417 | 2D stacked barcode for documents/IDs |
| `Barcode::EAN8` | `^B8` | EAN-8 | 8-digit European article number |
| `Barcode::UPCE` | `^B9` | UPC-E | Compressed 6-digit UPC for small packages |
| `Barcode::CODE93` | `^BA` | Code 93 | Compact alphanumeric, successor to Code 39 |
| `Barcode::CODABLOCK` | `^BB` | Codablock | Stacked Code 128 for dense alphanumeric data |
| `Barcode::CODE128` | `^BC` | Code 128 | Full ASCII, high-density linear; see [`^BC`](BC.md) |
| `Barcode::UPS` | `^BD` | UPS MaxiCode | Fixed-size 2D matrix used by UPS |
| `Barcode::EAN13` | `^BE` | EAN-13 | 13-digit European article number (retail) |
| `Barcode::MICROPDF417` | `^BF` | MicroPDF417 | Compact variant of PDF417 |
| `Barcode::INDUSTRIAL2` | `^BI` | Industrial 2 of 5 | Numeric-only, used in warehouses |
| `Barcode::STANDARD2` | `^BJ` | Standard 2 of 5 | Legacy numeric barcode |
| `Barcode::ANSI` | `^BK` | ANSI 3 of 9 | Extended Code 39 (full ASCII via two-character pairs) |
| `Barcode::LOGMARS` | `^BL` | LOGMARS | US DoD version of Code 39 |
| `Barcode::MSI` | `^BM` | MSI Plessey | Numeric barcode for retail shelf labels |
| `Barcode::PLESSEY` | `^BP` | UK Plessey | Hex (0–9, A–F) barcode for UK retail |
| `Barcode::QR` | `^BQ` | QR Code | 2D matrix, URLs / text; see [`^BQ`](BQ.md) |
| `Barcode::GS1` | `^BR` | GS1 DataBar | GS1 linear barcode family (formerly RSS) |
| `Barcode::UPC_EAN` | `^BS` | UPC/EAN extensions | 2- or 5-digit supplemental barcode |
| `Barcode::TLC39` | `^BT` | TLC39 | Combined linear + MicroPDF417 (DoD) |
| `Barcode::UPCA` | `^BU` | UPC-A | 12-digit universal product code (retail) |
| `Barcode::DATAMATRIX` | `^BX` | DataMatrix | 2D matrix for small items, electronics |
| `Barcode::DEFAULT` | `^BY` | Barcode Default | Width multiplier; see [`^BY`](BY.md) |
| `Barcode::POSTAL` | `^BZ` | USPS Postal | USPS POSTNET barcode |

---

## Generic `drawBarcode()` API

All barcode types can be printed with:

```php
$builder->drawBarcode(
    Barcode $type,
    float $x,
    float $y,
    float $height,
    string $data,
    bool $printData = false,
    bool $labelAbove = false,
    Orientation $orientation = Orientation::NORMAL,
    int $size = 0
): void
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$type` | `Barcode` | The barcode symbology (e.g. `Barcode::EAN13`). |
| `$x` | `float` | X position in the current unit. |
| `$y` | `float` | Y position in the current unit. |
| `$height` | `float` | Barcode height in the current unit. |
| `$data` | `string` | Data to encode. |
| `$printData` | `bool` | Print human-readable text below the barcode. |
| `$labelAbove` | `bool` | Print human-readable text above the barcode. |
| `$orientation` | `Orientation` | Rotation of the barcode. |
| `$size` | `int` | Module width multiplier (1–9); `0` uses the printer default. |

---

## Examples

### EAN-13

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Barcode;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::DOTS);
$builder->drawBarcode(Barcode::EAN13, 5, 50, 30, '5901234123457', true);
echo $builder->toZpl();
```

### DataMatrix

```php
$builder->drawBarcode(Barcode::DATAMATRIX, 10, 10, 20, 'ABC123');
```

### PDF417

```php
$builder->drawBarcode(Barcode::PDF417, 5, 80, 40, 'Hello, World!');
```

### UPC-A

```php
$builder->drawBarcode(Barcode::UPCA, 5, 50, 30, '012345678905', true);
```

---

## Notes

- The library automatically adjusts the ZPL parameter list for each symbology (e.g. truncating unsupported parameters for numeric-only types).
- For Code 39, Code 11, ANSI, and Plessey a check-digit parameter is inserted automatically.
- For QR Code the field data is prefixed with `QA,` and only the first 4 parameters are used.
- For MSI a check-digit type parameter (`B`) is inserted automatically.
