<?php

namespace Zpl\Enums;

enum Barcode: string
{
    case AZTEK = 'B0';
    case CODE11 = 'B1';
    case INTERLEAVED2 = 'B2';
    case CODE39 = 'B3';
    case CODE49 = 'B4';
    case PLANET = 'B5';
    case PDF417 = 'B7';
    case EAN8 = 'B8';
    case UPCE = 'B9';
    case CODE93 = 'BA';
    case CODABLOCK = 'BB';
    case CODE128 = 'BC';
    case UPS = 'BD';
    case EAN13 = 'BE';
    case MICROPDF417 = 'BF';
    case INDUSTRIAL2 = 'BI';
    case STANDARD2 = 'BJ';
    case ANSI = 'BK';
    case LOGMARS = 'BL';
    case MSI = 'BM';
    case PLESSEY = 'BP';
    case QR = 'BQ';
    case GS1 = 'BR';
    case UPC_EAN = 'BS';
    case TLC39 = 'BT';
    case UPCA = 'BU';
    case DATAMATRIX = 'BX';
    case DEFAULT = 'BY';
    case POSTAL = 'BZ';
}
