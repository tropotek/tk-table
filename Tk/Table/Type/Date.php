<?php

namespace Tk\Table\Type;

use Tk\Table\Cell;

class Date
{
    public static string $format = 'd/m/Y';

    public static function onValue(array|object $row, Cell $cell): string
    {
        $value = $row->{$cell->getName()};
        if ($value instanceof \DateTimeInterface) {
            $value = $value->format(self::$format);
        }
        return $value ?? '';
    }

}