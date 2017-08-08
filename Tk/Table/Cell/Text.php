<?php
namespace Tk\Table\Cell;


/**
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
     * Create
     *
     * @param string $property
     * @param string $label If null the property name is used EG: 'propName' = 'Prop Name'
     */
    public function __construct($property, $label = null)
    {
        parent::__construct($property, $label);
    }

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
     * @param mixed $obj
     * @param int|null $rowIdx The current row being rendered (0-n) If null no rowIdx available.
     * @return string
     */
    public function getCellHtml($obj, $rowIdx = null)
    {
        $value = $propValue = $this->getPropertyValue($obj, $this->getProperty());
        if ($this->charLimit && strlen($propValue) > $this->charLimit) {
            //$propValue = substr($propValue, 0, $this->charLimit-3).'...';
            $propValue = \Tk\Str::wordcat($value, $this->charLimit-3, '...');
        }
        //$this->setAttr('title', \Tk\Str::wordcat($value, 32, '...'));
        $this->setAttr('title', \Tk\Str::wordcat($this->getLabel(), 32, '...'));
        $str = htmlentities($propValue);
        $url = $this->getCellUrl($obj);
        if ($url) {
            //$str = sprintf('<a href="%s" title="%s">%s</a>', htmlentities($url->toString()), htmlentities($value), htmlentities($propValue));
            $str = sprintf('<a href="%s">%s</a>', htmlentities($url->toString()), htmlentities($propValue));
        }
        return $str;
    }

}