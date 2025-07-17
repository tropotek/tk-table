<?php

namespace Tk\Table\Type;

use Tk\Table\Cell;

/**
 * @deprecated Use Tk\Table\Type\Date
 */
class DateTime
{
    //public static string $format = 'j M Y H:i:s';
    public static string $format = 'j M Y g:ia';

    /**
     * @deprecated use Tk\Table\Type\Date::getMeridianDateTime()
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