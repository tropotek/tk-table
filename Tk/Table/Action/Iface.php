<?php
namespace Tk\Table\Action;

use Tk\Table;

/**
 * The interface for a table Action
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
abstract class Iface
{
    /**
     * This will be used for the event name using the instance ID
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $label = '';

    /**
     * @var array
     */
    protected $cssList = array();

    /**
     * @var Table
     */
    protected $table = null;

    
    /**
     * Create
     *
     * @param string $name The action event name
     */
    public function __construct($name)
    {
        $this->setName($name);
        $this->setLabel(ucfirst(preg_replace('/[A-Z]/', ' $0', $name)));
    }

    /**
     * Use this to init any code. This will be run on every page load.
     * 
     */
    public function init() {}

    /**
     * Execute the button event. This will only be called if the button name is in the request.
     * 
     * @return mixed
     */
    abstract public function execute();


    /**
     * @return string|\Dom\Template
     */
    abstract public function getHtml();


    /**
     * Has this button action been fired
     *
     * @return bool
     */
    public function hasFired()
    {
        $request = $this->getTable()->getRequest();
        return !empty($request[$this->getTable()->makeInstanceKey($this->getName())]);
    }

    /**
     * Set the id to be the same as the table. This will be used by the
     * cells for the event key
     *
     * @param Table $table
     */
    public function setTable($table)
    {
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
    
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = preg_replace('/[^a-z0-9_-]/i', '_', $name);
    }

    /**
     * Get the cell label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set the cell label
     *
     * @param string $str
     * @return $this
     */
    public function setLabel($str)
    {
        $this->label = $str;
        return $this;
    }

    /**
     * Add a css class
     *
     * @param string $class
     * @return $this
     */
    public function addCss($class)
    {
        $this->cssList[$class] = $class;
        return $this;
    }

    /**
     * remove a css class
     *
     * @param string $class
     * @return $this
     */
    public function removeCss($class)
    {
        unset($this->cssList[$class]);
        return $this;
    }

    /**
     * Get the css class list
     *
     * @return array
     */
    public function getCssList()
    {
        return $this->cssList;
    }

    /**
     * Set the css cell class list
     * If no parameter sent the array is cleared.
     *
     * @param array $arr
     * @return $this
     */
    public function setCssList($arr = array())
    {
        $this->cssList = $arr;
        return $this;
    }

}
