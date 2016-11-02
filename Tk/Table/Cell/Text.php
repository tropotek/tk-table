<?php
namespace Tk\Table\Cell;


/**
 * Class Text
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Text extends Iface
{

    /**
     * The max numbers of characters to display
     *      0 = no limit
     * @var int
     */
    protected $charLimit = 0;

    /**
     * Use 0 to disable character limit
     *
     * @param $i
     * @return $this
     */
    public function setCharacterLimit($i)
    {
        $this->charLimit = (int)$i;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getCellHeader()
    {
        // TODO: Not happy with this here?????
//        if ($this->getTable()->getList() instanceof \Tk\Db\Map\ArrayObject) {
//            /** @var \Tk\Db\Map\Mapper $mapper */
//            $mapper = $this->getTable()->getList()->getMapper();
//            if ($mapper instanceof \Ts\Db\Mapper) {
//                $mapProperty = $mapper->getDbMap()->getProperty($this->getOrderProperty());
//                if ($mapProperty) {
//                    $this->setOrderProperty($mapProperty->getColumnName());
//                }
//            }
//        }

        $str = str_replace(array('id', 'Id'), '', $this->getLabel());
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
        $value = $propValue = $this->getPropertyValue($obj, $this->getProperty());
        if ($this->charLimit && strlen($propValue) > $this->charLimit) {
            $propValue = substr($propValue, 0, $this->charLimit-3).'...';
        }
        $this->addCellAttribute('title', $value);
        $str = htmlentities($propValue);
        $url = $this->getCellUrl($obj);
        if ($url) {
            $str = sprintf('<a href="%s" title="%s">%s</a>', htmlentities($url->toString()), htmlentities($value), htmlentities($propValue));
        }
        return $str;
    }

}