# `^CI` — Change International Font / Character Encoding

## Overview

The `^CI` (Change International Font) command selects the character set (encoding) used when rendering text on the label. This is essential for printing non-ASCII characters such as accented letters, Cyrillic, Asian scripts, etc.

---

## ZPL Syntax

```
^CIa
```

| Parameter | Description |
|-----------|-------------|
| `a` | Encoding identifier (integer, 0–36) |

Common encoding identifiers:

| Code | Encoding |
|------|----------|
| `0` | USA / Western Europe (default) |
| `13` | Latin-1 (ISO 8859-1) |
| `14` | Latin-2 (ISO 8859-2) |
| `15` | Latin-3 (ISO 8859-3) |
| `28` | Unicode (UTF-8) |
| `33` | Latin-9 (ISO 8859-15) |

Refer to the Zebra ZPL II Programming Guide for the complete list.

---

## PHP API

```php
$builder->setEncoding(int $code): void
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$code` | `int` | Encoding identifier (0–36). |

---

## Examples

### Set UTF-8 encoding

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::MM);
$builder->setEncoding(28);
$builder->drawText(5, 5, 'Héllo Wörld');
echo $builder->toZpl();
```

Output:

```
^XA
^CI28
^FWN
^FO5,5
^FH^FDHéllo Wörld^FS
^FWN
^XZ
```

### Set Latin-1 encoding

```php
$builder->setEncoding(13);
// Output: ^CI13
```

---

## Notes

- Set encoding once at the start of the label, before any text commands.
- When using special control characters (`^`, `~`, `_`, `{`, `}`, etc.) in text, the library automatically escapes them to hex sequences via `^FH`. See [`^FH`](FH.md) for details.
