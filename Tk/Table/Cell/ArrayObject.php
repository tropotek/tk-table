<?php
namespace Tk\Table\Cell;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class ArrayObject extends Text
{
    // NOTE: This change from getPropertyValue() was made to avoid csv issue with arrays


    /**
     * @param object $obj
     * @param null $property
     * @param bool $withCallable
     * @return mixed|string
     */
    public function getObjectPropertyValue($obj, $property = null, $withCallable = true)
    {
        $value = parent::getObjectPropertyValue($obj, $property, $withCallable);
        if (is_array($value)) {
            $value = implode(', ', $value);
        }
        return $value;
    }

}