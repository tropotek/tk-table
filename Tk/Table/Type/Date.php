<?php

namespace Tk\Table\Type;

use DateTimeInterface;
use Tk\Table\Cell;

class Date
{
    /**
     * @deprecated use Tk\Table\Type\Date::getAuDate()
     */
    public static function onValue(array|object $row, Cell $cell): string
    {
        return self::getAuDate($row, $cell);
    }

    // new callables

    protected static function formatDate(?DateTimeInterface $date, string $format = 'd/m/Y'): string
    {
        if ($date instanceof \DateTimeInterface) {
            return $date->format($format);
        }
        return '';
    }

    /**
     * 2025-10-01
     */
    public static function getISODate(array|object $row, Cell $cell): string
    {
        return self::formatDate($row->{$cell->getName()}, \Tk\Date::FORMAT_ISO_DATE);
    }

    /**
     * 2025-10-01 20:10:10
     */
    public static function getISODateTime(array|object $row, Cell $cell): string
    {
        return self::formatDate($row->{$cell->getName()}, \Tk\Date::FORMAT_ISO_DATETIME);
    }

    /**
     * 5 Nov 2018
     */
    public static function getLongDate(array|object $row, Cell $cell): string
    {
        return self::formatDate($row->{$cell->getName()}, \Tk\Date::FORMAT_LONG_DATE);
    }

    /**
     * 5 Nov 2018 12:30 AM
     */
    public static function getLongDateTime(array|object $row, Cell $cell): string
    {
        return self::formatDate($row->{$cell->getName()}, \Tk\Date::FORMAT_LONG_DATETIME);
    }

    /**
     * 5 Nov 2018 22:30:20
     */
    public static function get24hDateTime(array|object $row, Cell $cell): string
    {
        return self::formatDate($row->{$cell->getName()}, 'j M Y H:i:s');
    }

    /**
     * d/m/Y
     */
    public static function getAuDate(array|object $row, Cell $cell): string
    {
        return self::formatDate($row->{$cell->getName()}, \Tk\Date::FORMAT_AU_DATE);
    }

    /**
     * m/d/Y
     */
    public static function getUsDate(array|object $row, Cell $cell): string
    {
        return self::formatDate($row->{$cell->getName()}, 'm/d/Y');
    }

    /**
     * 22:00
     */
    public static function getTime(array|object $row, Cell $cell): string
    {
        return self::formatDate($row->{$cell->getName()}, 'H:i');
    }

    /**
     * 12:30 PM
     */
    public static function getMeridianTime(array|object $row, Cell $cell): string
    {
        return self::formatDate($row->{$cell->getName()}, 'g:i A');
    }

}