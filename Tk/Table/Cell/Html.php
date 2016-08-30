<?php
namespace Tk\Table\Cell;


/**
 * Class Text
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Html extends Text
{

    /**
     * @param mixed $obj
     * @return string
     */
    public function getCellHtml($obj)
    {
        $propValue = $this->getPropertyValue($obj, $this->getProperty());
        $str = $propValue;
        $url = $this->getCellUrl($obj);
        if ($url) {
            $str = sprintf('<a href="%s" title="%s">%s</a>', htmlentities($url->toString()), htmlentities($this->getLabel()), htmlentities($propValue));
        }
        return $str;
    }

    /**
     * @param mixed $obj
     * @return string
     */
    public function getCellCsv($obj)
    {
        return $this->getPropertyValue($obj, $this->getProperty());
    }

}