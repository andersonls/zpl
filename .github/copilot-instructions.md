# Copilot Instructions

## Commands

```bash
composer run tests        # run all tests
composer run format       # fix code style with Laravel Pint
composer run analyse      # PHPStan static analysis (level 8)

./vendor/bin/phpunit --filter testMethodName   # run a single test
```

## Architecture

This is a PHP 8.2+ library that generates **ZPL (Zebra Programming Language)** strings for label printers and optionally sends them over TCP.

- **`AbstractBuilder`** — defines the public draw/set API (abstract methods for text, shapes, barcodes, graphics, etc.) and tracks cursor state (`x`, `y`, `margin`) in user units.
- **`ZplBuilder`** — the main class. Extends `AbstractBuilder`, accumulates ZPL command strings in `$commands[]`, and assembles them into a full ZPL document with `toZpl()`. The output structure is: `preCommands → ^XA → commands → ^XZ → postCommands`. Multi-page labels insert a `PAGE_SEPARATOR` placeholder that gets replaced at render time.
- **`PdfBuilder`** — alternative builder that renders the same API to PDF.
- **`Printer`** — sends ZPL to a network printer over a raw TCP socket on port 9100 (`Printer::printer('host')->send($zpl)`).
- **`PrinterStatus`** — parses the `~HS` status response from a printer.
- **`Commands/GraphicField`** — converts PNG/GIF images to the ZPL `^GF` (Graphic Field) command format.
- **`Enums/`** — `Unit` (DOTS/MM), `Barcode`, `Orientation`, `Align` — use these instead of raw strings.
- **`Fonts/`** — `AbstractMapper` + `Generic` and `Bematech` subclasses that map logical font IDs to ZPL font letters.

## Key Conventions

**Coordinate units**: All public API coordinates are in the user unit set at construction (`Unit::MM` or `Unit::DOTS`). Internally, `toDots()` converts: `mm * dpi / 25.4`. Default DPI is 203; call `setDpi()` to change it.

**Text escaping**: Special characters (`_`, `^`, `~`, `{`, `}`, `[`, `]`, `#`, `%`) must be escaped to ZPL hex sequences. This is handled automatically by `CONTROL_CHAR_HEX_MAPPINGS` + `^FH` prefix whenever `drawText()` or `drawCell()` is called. Always use those methods rather than raw `addCommand()` for text content.

**Font size formula**: `round(ptSize * (dpi * 0.014))` — applied inside `setFont()`.

**Dynamic ZPL commands**: Any unknown method call on `ZplBuilder` is forwarded to `__call`, which treats the method name as a ZPL command letter and the arguments as parameters (e.g., `$builder->CF('A', 30)` → `^CFA,30`). Macros registered via `ZplBuilder::macro()` take priority over this fallback.

**Pre/post commands**: Use `addPreCommand()` / `addPostCommand()` for commands that must wrap the `^XA`/`^XZ` block (e.g., `^LH`, `^PQ`). Avoid injecting `^XA`/`^XZ` manually.

**Testing**: Tests extend `Zpl\Tests\TestCase`, which provides `$this->driver` (a fresh `ZplBuilder` in DOTS mode) and `$this->getZpl()`. Test method names are **camelCase** (enforced by Pint). Use the `#[Test]` attribute rather than the `test` prefix.

**PHPStan**: Runs at level 8 on `src/` only. The one suppressed error pattern is for `class-string|object` method calls (used in font mapper reflection).
