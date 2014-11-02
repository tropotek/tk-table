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
class Date extends Iface
{
    /**
     * @var string
     */
    protected $format = \Tk\Date::MED_DATE;


    /**
     * Change the format of the date
     *
     * @param $format
     * @return $this
     */
    public function setFormat($format)
    {
        $this->format = $format;
        return $this;
    }


    /**
     * Get the property value from the object using the supplied property name
     *
     * @param \Tk\Date $obj
     * @return string
     */
    public function getPropertyValue($obj)
    {
        $value = parent::getPropertyValue($obj);
        $str = "";
        if ($value instanceof \Tk\Date) {
            $str = $value->toString($this->format);
        }
        return $str;
    }

    /**
     * Returns a property value ready to insert into a csv file
     * Override this for types that you want to render differently in csv
     *
     *
     * @param stdClass $obj
     * @return string
     */
    public function getCsv($obj)
    {
        $value = parent::getPropertyValue($obj);
        $str = "";
        if ($value instanceof \Tk\Date) {
            $str = $value->toString();
        }
        return $str;
    }

}
