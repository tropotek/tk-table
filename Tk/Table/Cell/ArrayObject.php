<?php
namespace Tk\Table\Cell;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class ArrayObject extends Text
{


    public function getPropertyValue($obj)
    {
        $value = $this->getObjectPropertyValue($obj);
        if (is_array($value)) {
            $value = implode(', ', $value);
        }
        return $value;
    }

}