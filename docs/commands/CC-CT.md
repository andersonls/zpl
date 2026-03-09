# `^CC` / `~CC` / `^CT` / `~CT` — Change Caret / Change Tilde

> **ℹ️ Not yet implemented as dedicated methods.**
> Use `addCommand()` / `addPreCommand()` to emit these commands directly.

## Overview

ZPL uses two special prefix characters:

- **Caret (`^`)** — format command prefix (e.g. `^XA`, `^FO`, `^FD`).
- **Tilde (`~`)** — device/control command prefix (e.g. `~SD`, `~JR`).

The `^CC` / `~CC` (Change Caret) and `^CT` / `~CT` (Change Tilde) commands replace these prefixes with an alternative character. This is useful when label data contains literal `^` or `~` characters that would otherwise be misinterpreted as ZPL commands.

> **Note:** In most cases the library's automatic hex-escaping (via `^FH`) handles these characters safely. Use `^CC`/`^CT` only when you need to change the prefix globally for the entire label.

---

## ZPL Syntax

### Change Caret

```
^CCa
~CCa
```

| Parameter | Description | Default |
|-----------|-------------|---------|
| `a` | New caret character (ASCII decimal value or literal) | `^` (ASCII 94) |

### Change Tilde

```
^CTa
~CTa
```

| Parameter | Description | Default |
|-----------|-------------|---------|
| `a` | New tilde character (ASCII decimal value or literal) | `~` (ASCII 126) |

---

## PHP API

These commands are not yet implemented as dedicated methods. Use one of these approaches:

```php
// Change caret to @ (using addPreCommand so it takes effect before ^XA)
$builder->addPreCommand('^CC@');

// Change tilde to !
$builder->addPreCommand('^CT!');

// Reset to defaults
$builder->addPreCommand('^CC^');
$builder->addPreCommand('^CT~');
```

---

## Examples

### Using `@` as the caret character

```
^CC@    ; change caret to @
@XA
@CF0,40
@FO10,10@FH@FDPrice: 50^  off@FS   ; ^ is now a literal character
@XZ
```

### Restoring defaults mid-label

```
^XA
^CC@        ; switch to @
@FO10,10
@FH@FD100^ discount@FS
@CC^        ; restore ^ as caret
^FO10,60
^FH^FDNormal field^FS
^XZ
```

### Via PHP

```php
use Zpl\ZplBuilder;
use Zpl\Enums\Unit;

$builder = new ZplBuilder(Unit::DOTS);
// Change caret before ^XA
$builder->addPreCommand('^CC@');
// Now all commands in the label must use @ as the caret
// Better approach: use the library's automatic ^FH escaping instead
```

---

## Notes

- After changing the caret/tilde, **all** subsequent ZPL commands must use the new prefix character — including `^XA` and `^XZ`.
- Reset to defaults (`^CC^` and `^CT~`) before sending the next label to avoid persistent side effects.
- The library's `drawText()` and `drawCell()` methods automatically escape `^` and `~` in field data via `^FH` hex sequences. This is the recommended approach for most use cases and avoids the need to change the caret/tilde globally.
- `^CC` / `^CT` are most useful when working with raw ZPL strings that already use an alternative prefix, or when interfacing with legacy label formats.
