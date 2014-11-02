<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Table\Cell;

/**
 * The dynamic table Cell
 *
 *
 * @package Table
 */
class Boolean extends Iface
{

    /**
     * Get the property value from the object using the supplied property name
     *
     * @param stdClass $obj
     * @return string
     */
    public function getPropertyValue($obj)
    {
        $value = parent::getPropertyValue($obj);
        if ($value !== '' && $value !== null) {
            if ($value) {
                $value = 'Yes';
            } else {
                $value = 'No';
            }
        }
        return $value;
    }


}
