<?php
namespace Tk\Event;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class TableEvent extends Iface
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
        parent::__construct();
        $this->table = $table;
    }

    /**
     * @return null|\Tk\Table
     */
    public function getForm()
    {
        return $this->table;
    }

}