## This library is a php wrapper for the ZPL Programming Language.
---
## Installation:

```bash
composer require Stowers-LIMS/zpl
```

## How to use:

```php
$driver = new \Zpl\ZplBuilder('mm');
$driver->setEncoding(28);
$driver->setFontMapper(new \Zpl\Fonts\Bematech\Lb1000());

$driver->SetFont('Arial',16);
$driver->SetXY(0, 0);
$driver->drawCell(100, 10, 'Hello World', true, true, 'C');

\Zpl\Printer::printer('192.168.1.1')->send($driver->toZpl());
```

## Credit:

This package is a fork of Anderson Luiz Silvério's [ZPL library](https://github.com/andersonls/zpl).