<?php

namespace Tk\Table\Type;

use Tk\Table\Cell;

/**
 * @deprecated Use Tk\Table\Type\Date
 */
class DateFmt
{
    public static string $format = 'j M Y';

    /**
     * @deprecated use Tk\Table\Type\Date::getLongDate()
     */
    public static function onValue(array|object $row, Cell $cell): string
    {
        $value = $row->{$cell->getName()};
        if ($value instanceof \DateTimeInterface) {
            $value = $value->format(self::$format);
        }
        return $value ?? '';
    }

}