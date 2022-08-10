<?php
namespace Tk\Table\Cell;


/**
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Link extends Text
{
    /**
     * @param string $property
     * @param string $label If null the property name is used EG: 'propName' = 'Prop Name'
     */
    public function __construct($property, $label = null)
    {
        parent::__construct($property, $label);
        $this->setOrderProperty('');
    }

    /**
     * @param mixed $obj
     * @param int|null $rowIdx The current row being rendered (0-n) If null no rowIdx available.
     * @return string
     */
    public function getCellHtml($obj, $rowIdx = null)
    {
        $propValue = $this->getPropertyValue($obj);
        $str = sprintf('<a href="%s" target="_blank">%s</a>', $propValue, $propValue);
        return $str;
    }


}