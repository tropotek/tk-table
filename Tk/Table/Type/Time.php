<?php

namespace Tk\Table\Type;

use Tk\Table\Cell;

/**
 * @deprecated Use Tk\Table\Type\Date
 */
class Time
{
    /**
     * alt format 'H:i:s' 24 hour time or 'g:ia' for meridian time
     */
    public static string $format = 'H:i';


    /**
     * @deprecated use Tk\Table\Type\Date::getTime()
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