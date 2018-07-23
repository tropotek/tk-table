<?php
namespace Tk\Table\Cell;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Link extends Text
{

    /**
     * @param mixed $obj
     * @param int|null $rowIdx The current row being rendered (0-n) If null no rowIdx available.
     * @return string
     */
    public function getCellHtml($obj, $rowIdx = null)
    {
        $propValue = $this->getPropertyValue($obj, $this->getProperty());
        $str = sprintf('<a href="%s" target="_blank">%s</a>', $propValue, $propValue);
        return $str;
    }


}