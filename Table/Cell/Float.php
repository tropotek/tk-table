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
class Float extends Iface
{

    /**
     * @var int
     */
    protected $places = 2;


    public function __construct($property, $label = null, $places = 2)
    {
        parent::__construct($property, $label);
        $this->places = $places;
    }


    /**
     * Get the property value from the object using the supplied property name
     *
     * @param stdClass $obj
     * @return string
     */
    public function getPropertyValue($obj)
    {
        $value = parent::getPropertyValue($obj);
        if ($value) {
            $value = round((float)$value, $this->places);
        }
        return $value;
    }


}
