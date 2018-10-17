<?php
namespace Tk\Table\Cell;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Minutes extends Text
{

    public function getPropertyValue($obj)
    {
        $value = $this->getRawValue($obj);
        $value = gmdate("G:i", (int)$value*60);
        return $value;
    }
}