<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Table;

/**
 * Interface for Table elements
 *
 *
 * @package Table
 */
abstract class Element extends \Tk\Object
{
    
    /**
     * @var Table
     */
    protected $table = null;
    
    
    
    /**
     * Execute the Table Element
     */
    abstract function execute($list);
    
    
    
    /**
     * Set the id to be the same as the table. This will be used by the
     * cells for the event key
     *
     * @param Table $table
     */
    public function setTable($table)
    {
        $this->setInstanceId($table->getInstanceId());
        $this->table = $table;
    }
    
    /**
     * Get the parent table object
     *
     * @return Table
     */
    public function getTable()
    {
        return $this->table;
    }
    
    
    
}