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
 * @package Table\Cell
 */
class Money extends Iface
{


    /**
     * Get the property value from the object using the supplied property name
     *
     * @param \Tk\Money $obj
     * @return string
     */
    public function getPropertyValue($obj)
    {
        $value = parent::getPropertyValue($obj);
        $str = "";
        if ($value instanceof \Tk\Money) {
            $str = $value->toString();
        }
        return $str;
    }

}
