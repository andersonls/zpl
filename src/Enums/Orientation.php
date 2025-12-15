<?php

namespace Zpl\Enums;

enum Orientation: string
{
    case NORMAL = 'N'; // normal as default
    case ROTATED = 'R'; // rotated 90 degrees
    case INVERTED = 'I'; // inverted 180 degrees
    case BOTTOM_UP = 'B'; // bottom-up 270 degrees, read from bottom up
}
