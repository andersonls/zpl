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

| Code | Encoding | Notes |
|------|----------|-------|
| `0` | USA / Western Europe | Default; covers printable ASCII + common Latin characters |
| `1` | Multinational | Similar to code 0; extended Latin coverage |
| `2` | Graphic characters | Special symbols and line-drawing characters |
| `3` | US Legal | Legal document character set |
| `4` | IBM PC-437 | OEM IBM original (USA); compatible with DOS-era characters |
| `5` | IBM PC-850 | OEM Multilingual Latin 1 (Western Europe) |
| `6` | IBM PC-860 | OEM Portuguese |
| `7` | IBM PC-863 | OEM Canadian-French |
| `8` | IBM PC-865 | OEM Nordic |
| `9` | IBM PC-857 | OEM Turkish |
| `10` | IBM PC-851 | OEM Greek |
| `11` | IBM PC-737 | OEM Greek (alternate) |
| `12` | IBM PC-852 | OEM Latin 2 (Central/Eastern Europe) |
| `13` | ISO 8859-1 | Latin 1 — Western European languages |
| `14` | ISO 8859-2 | Latin 2 — Central/Eastern European languages |
| `15` | ISO 8859-4 | Latin 4 — Baltic languages |
| `16` | ISO 8859-5 | Cyrillic (Russian, Bulgarian, Serbian, etc.) |
| `17` | ISO 8859-7 | Greek |
| `18` | ISO 8859-8 | Hebrew (logical order) |
| `19` | ISO 8859-9 | Latin 5 — Turkish |
| `20` | ISO 8859-10 | Latin 6 — Nordic languages |
| `21` | Windows-1250 | Central/Eastern Europe (CP1250) |
| `22` | Windows-1251 | Cyrillic (CP1251) |
| `23` | Windows-1252 | Western Europe — Latin 1 (CP1252) |
| `24` | Windows-1253 | Greek (CP1253) |
| `25` | Windows-1254 | Turkish (CP1254) |
| `26` | Windows-1255 | Hebrew (CP1255) |
| `27` | Windows-1256 | Arabic (CP1256) |
| `28` | Unicode / UTF-8 | Full Unicode character set using UTF-8 encoding |
| `29` | UTF-32 Big Endian | 4-byte Unicode, big-endian byte order |
| `30` | UTF-32 Little Endian | 4-byte Unicode, little-endian byte order |
| `31` | UTF-16 Big Endian | 2-byte Unicode (BMP), big-endian byte order |
| `32` | UTF-16 Little Endian | 2-byte Unicode (BMP), little-endian byte order |
| `33` | ISO 8859-15 | Latin 9 — Western Europe with Euro sign (€) |
| `34` | ISO 8859-16 | Latin 10 — South-Eastern Europe |
| `35` | Windows-1257 | Baltic (CP1257) |
| `36` | Windows-1258 | Vietnamese (CP1258) |

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
