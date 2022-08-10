<?php
namespace Tk\Table\Cell;


/**
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Date extends Text
{
    const FORMAT_RELATIVE = 'relative';

    /**
     * @var string
     */
    protected $format = 'Y-m-d h:i:s';

    /**
     * @var string
     */
    protected $rawFormat = 'Y-m-d h:i:s';

    /**
     * @param string $property
     * @param null|string $format
     * @return Date
     */
    public static function createDate($property, $format = 'Y-m-d')
    {
        $obj = new self($property);
        if ($format)
            $obj->setFormat($format);
        return $obj;
    }

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
     * @param string $rawFormat
     * @return $this
     */
    public function setRawFormat($rawFormat)
    {
        $this->rawFormat = $rawFormat;
        return $this;
    }

    /**
     * Get the property value from the object using the supplied property name
     *
     * @param \DateTime $obj
     * @param string $property
     * @return string
     */
    public function getPropertyValue($obj)
    {
        $value = parent::getPropertyValue($obj);
        if ($value && !$value instanceof \DateTime) {
            $value = \Tk\Date::create($value);
        }

        if ($value instanceof \DateTime) {
            if ($this->format == self::FORMAT_RELATIVE) {
                return \Tk\Date::toRelativeString($value);
            } else {
                return $value->format($this->format);
            }
        }
        return $value;
    }


    /**
     * Get the raw string property value.
     * This call can be used for exporting data into a csv, json, xml format
     *
     * @param mixed $obj
     * @return string
     */
    public function getRawValue($obj)
    {
        $value = parent::getRawValue($obj);

        if ($value && !$value instanceof \DateTime) {
            $value = \Tk\Date::create($value);
        }
        if ($value instanceof \DateTime) {
            if ($this->rawFormat == self::FORMAT_RELATIVE) {
                return \Tk\Date::toRelativeString($value);
            } else {
                return $value->format($this->rawFormat);
            }
        }
        return $value;
    }


}