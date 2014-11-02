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
class DataImage extends Iface
{


    /**
     * getPropertyValue
     *
     * @param Object $obj
     * @return string
     */
    public function getPropertyValue($obj)
    {
        $value = parent::getPropertyValue($obj);
        return $value;
    }



    /**
     * Get the table data from an object if available
     *   Overide getTd() to add data to the cell.
     *
     * @param \Tk\Object $obj
     * @return Dom_Template Alternativly you can return a plain HTML string
     */
    public function getTd($obj)
    {
        $this->rowClass = array(); // reset row class list
        $str = '';

        $url = $this->getUrl();
        if ($url) {
            if (count($this->urlPropertyList)) {
                foreach ($this->urlPropertyList as $prop) {
                    $url->set($prop, $this->getPropertyValue($obj));
                }
            } else {
                $pos = strrpos(get_class($obj), '_');
                $name = substr(get_class($obj), $pos + 1);
                $prop = strtolower($name[0]) . substr($name, 1) . 'Id';
                $url->set($prop, $obj->getId());
            }

            $url = Tk\Url::createDataUrl($this->getPropertyValue($obj));
            $str = '<a href="' . htmlentities($url->toString()) . '"><img src="'.$url->toString().'" alt="" height="50" /></a>';
        } else {
            $url = Tk\Url::createDataUrl($this->getPropertyValue($obj));
            $str = '<a href="' . $url->toString() . '" class="lightbox"><img src="'.$url->toString().'" alt="" height="50" /></a>';
        }
        return $str;
    }

}
