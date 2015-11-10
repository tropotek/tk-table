<?php
namespace Tk\Table\Cell;


/**
 * Class Text
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Boolean extends Text
{

    public function getPropertyValue($obj, $property)
    {
        $value = parent::getPropertyValue($obj, $property);
        if ($value) {
            if ($value == true || $value == 'Yes' || $value == '1' || $value == 'ok' || $value == 'Y') {
                return 'Yes';
            }
        }
        return 'No';
    }
}