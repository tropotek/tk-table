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
class Url extends Iface
{

    /**
     * get the table data from an object if available
     *   Overide getTd() to add data to the cell.
     *
     * @param Object $obj
     * @return \Dom\Template Alternativly you can return a plain HTML string
     */
    public function getTd($obj)
    {
        $this->rowClass = array(); // reset row class list
        $url = $this->getPropertyValue($obj);

        return '<a href="' . $url . '" title="Visit Website" target="_blank">' . $url . '</a>';
    }

}
