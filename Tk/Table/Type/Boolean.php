<?php

namespace Tk\Table\Type;

use Tk\Table\Cell;

class Boolean
{

    public static function onValue(array|object $row, Cell $cell): string
    {
        $value = $row->{$cell->getName()};
        return (bool)$value ? 'Yes' : 'No';
    }

}