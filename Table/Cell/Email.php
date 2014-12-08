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
class Email extends Iface
{
    /**
     * get the table data from an object if available
     *   Overide getTd() to add data to the cell.
     *
     * @param Object $placement
     * @return \Dom\Template Alternativly you can return a plain HTML string
     */
    public function getTd($placement)
    {
        $this->rowClass = array(); // reset row class list
        $email = $this->getPropertyValue($placement);
        return '<a href="mailto:' . $email . '" title="Compose an email to this address.">' . $email . '</a>';
    }

}
