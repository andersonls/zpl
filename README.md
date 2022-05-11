## This library is a php wrapper for the ZPL Programming Language.
---
## Installation:

```bash
composer require andersonls/zpl
```

## How to use:

```php
$driver = new \Zpl\ZplBuilder('mm');
$driver->setEncoding(28);
$driver->setFontMapper(new \Zpl\Fonts\Generic());

$driver->SetFont('0',16);
$driver->SetXY(0, 0);
$driver->drawCell(100, 10, 'Hello World', true, true, 'C');

\Zpl\Printer::printer('192.168.1.1')->send($driver->toZpl());
```

## Donations

If this project helps you somehow, you can give me a cup of coffee :)

[![paypal](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/donate/?hosted_button_id=KCZB8TGG63Y7W)
