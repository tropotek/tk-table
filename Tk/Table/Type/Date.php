<?php

namespace Tk\Table\Type;

use DateTimeInterface;
use Tk\Table\Cell;

class Date
{

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
     * @param list<mixed>|object $row
     */
    public static function getISODate(array|object $row, Cell $cell): string
    {
        return self::formatDate($row->{$cell->getName()}, \Tk\Date::FORMAT_ISO_DATE);
    }

    /**
     * 2025-10-01 20:10:10
     * @param list<mixed>|object $row
     */
    public static function getISODateTime(array|object $row, Cell $cell): string
    {
        return self::formatDate($row->{$cell->getName()}, \Tk\Date::FORMAT_ISO_DATETIME);
    }

    /**
     * 5 Nov 2018
     * @param list<mixed>|object $row
     */
    public static function getLongDate(array|object $row, Cell $cell): string
    {
        return self::formatDate($row->{$cell->getName()}, \Tk\Date::FORMAT_LONG_DATE);
    }

    /**
     * 5 Nov 2018 12:30 AM
     * @param list<mixed>|object $row
     */
    public static function getLongDateTime(array|object $row, Cell $cell): string
    {
        return self::formatDate($row->{$cell->getName()}, \Tk\Date::FORMAT_LONG_DATETIME);
    }

    /**
     * 5 Nov 2018 22:30:20
     * @param list<mixed>|object $row
     */
    public static function get24hDateTime(array|object $row, Cell $cell): string
    {
        return self::formatDate($row->{$cell->getName()}, 'j M Y H:i:s');
    }

    /**
     * d/m/Y
     * @param list<mixed>|object $row
     */
    public static function getAuDate(array|object $row, Cell $cell): string
    {
        return self::formatDate($row->{$cell->getName()}, \Tk\Date::FORMAT_AU_DATE);
    }

    /**
     * m/d/Y
     * @param list<mixed>|object $row
     */
    public static function getUsDate(array|object $row, Cell $cell): string
    {
        return self::formatDate($row->{$cell->getName()}, 'm/d/Y');
    }

    /**
     * 22:00
     * @param list<mixed>|object $row
     */
    public static function getTime(array|object $row, Cell $cell): string
    {
        return self::formatDate($row->{$cell->getName()}, 'H:i');
    }

    /**
     * 12:30 PM
     * @param list<mixed>|object $row
     */
    public static function getMeridianTime(array|object $row, Cell $cell): string
    {
        return self::formatDate($row->{$cell->getName()}, 'g:i A');
    }

}