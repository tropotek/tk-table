<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Table\EditCell;

/**
 * The dynamic table Cell
 *
 *
 * @package Table\EditCell
 */
class String extends \Table\Cell\Iface
{

    /**
     * get the table data from an object if available
     *   Overide getTd() to add data to the cell.
     *
     * @param \Tk\Object $obj
     * @return \Dom\Template Alternativly you can return a plain HTML string
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
                $class = get_class($obj);
                $pos = strrpos($class, '\\');
                if (!$pos === false) {
                    $name = substr(get_class($obj), $pos + 1);
                } else {
                    $name = $class;
                }
                $prop = strtolower($name[0]) . substr($name, 1) . 'Id';
                $url->set($prop, $obj->id);
            }
            $str = '<a href="' . htmlentities($url->toString()) . '">' . htmlentities($this->getPropertyValue($obj)) . '</a>';
        } else {
            $str = htmlentities($this->getPropertyValue($obj));
        }
        return $str;
    }

}
