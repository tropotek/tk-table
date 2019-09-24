<?php
namespace Tk\Table\Cell;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Minutes extends Text
{

    public function getPropertyValue($obj)
    {
        $value = self::min2Str($this->getRawValue($obj));
        return $value;
    }

    /**
     * Convert minutes to a `h:m` string
     *
     * @param int $minutes
     * @return string
     */
    public static function min2Str($minutes)
    {
        $h = floor($minutes / 60);
        $m = $minutes - ($h * 60);
        return sprintf('%02d:%02d', $h, $m);
    }

    /**
     * Convert a string in the format of `h:m` to minutes
     *
     * @param $str
     * @return int
     */
    public static function str2min($str)
    {
        list($h, $m) = explode(':', $str);
        return (int)(((int)$h*60)+(int)$m);
    }

}