<?php
namespace Tk\Event;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class TableEvent extends Event
{

    /**
     * @var null|\Tk\Table
     */
    protected $table = null;

    /**
     * @param \Tk\Table $table
     */
    public function __construct($table)
    {
        $this->table = $table;
    }

    /**
     * @return null|\Tk\Table
     */
    public function getTable()
    {
        return $this->table;
    }

}