<?php
namespace Tk\Table\Cell;


/**
 * Class Text
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Boolean extends Text
{

    public function getPropertyValue($obj, $property)
    {
        $value = parent::getPropertyValue($obj, $property);

        if ($value === null) return '';

        if ($value) {
            if ($value == true || strtolower($value) == 'yes' || strtolower($value) == 'true' ||
                strtolower($value) == 't' || $value == '1' || strtolower($value) == 'ok' || strtolower($value) == 'y' ||
                $value == $property)
            {
                return 'Yes';
            }
        }
        return 'No';
    }
}