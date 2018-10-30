<?php
namespace Tk\Table\Cell;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
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
     * @return int
     */
    public function getCharLimit()
    {
        return $this->charLimit;
    }


    /**
     * @param mixed $obj
     * @param int|null $rowIdx The current row being rendered (0-n) If null no rowIdx available.
     * @return string
     */
    public function getCellHtml($obj, $rowIdx = null)
    {
        $value = $propValue = $this->getPropertyValue($obj);

        if ($this->charLimit && strlen($propValue) > $this->charLimit) {
            $propValue = \Tk\Str::wordcat($propValue, $this->charLimit - 3, '...');
        }
        if (!$this->hasAttr('title')) {
            $this->setAttr('title', htmlentities($propValue));
        }

        $str = htmlentities($propValue);
        $url = $this->getCellUrl($obj);
        if ($url) {
            $str = sprintf('<a href="%s">%s</a>', htmlentities($url->toString()), htmlentities($propValue));
        }
        return $str;
    }



}