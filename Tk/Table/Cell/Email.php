<?php
namespace Tk\Table\Cell;


/**
 * Class Text
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Email extends Text
{

    /**
     * @param mixed $obj
     * @return string
     */
    public function getCellHtml($obj)
    {
        $propValue = $this->getPropertyValue($obj, $this->getProperty());
        $str = sprintf('<a href="mailto:%s" title="Compose an email to this address.">%s</a>', $propValue, $propValue);
        return $str;
    }


}