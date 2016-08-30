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
     *
     * @return string
     */
    public function getCellHeader()
    {
        $str = $this->getLabel();
        $url = $this->getOrderUrl();
        if ($url) {
            $str = sprintf('<a href="%s" class="noblock" title="Click to order by: %s">%s</a>', htmlentities($url->toString()), $this->getOrderProperty(), $this->getLabel());
        }
        return $str;
    }

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

}