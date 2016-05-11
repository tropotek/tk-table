<?php
namespace Tk\Table\Renderer;

use \Tk\Table;
use \Tk\Table\Cell;

/**
 * Class Iface
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
abstract class Iface extends \Dom\Renderer\Renderer
{

    /**
     * @var Table
     */
    protected $table = null;

    /**
     * @var array
     */
    protected $footRenderList = array();

    /**
     * Keep track of the currently rendered row
     * @var int
     */
    protected $rowId = 0;


    /**
     * construct
     *
     * @param Table $table
     */
    public function __construct($table = null)
    {
        $this->setTable($table);
    }

    /**
     * Set the table object
     *
     * @param Table $table
     * @return boolean Return true on successful setting of table object.
     */
    public function setTable($table)
    {
        if (!$table instanceof Table) return false;

        $this->table = $table;
        $this->table->setParam('renderer', $this);
        $this->initFilterForm();

        return true;
    }


    /**
     * Get the table
     *
     * @return Table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Append a renderer to the footer renderer list
     *
     * @param mixed $renderer
     */
    public function appendFootRenderer($renderer)
    {
        $this->footRenderList[] = $renderer;
    }

    /**
     * @return array
     */
    public function getFooterRenderList()
    {
        return $this->footRenderList;
    }

    /**
     * @param array $footRenderList
     * @return $this
     */
    public function setFooterRenderList($footRenderList)
    {
        $this->footRenderList = $footRenderList;
        return $this;
    }

    /**
     * Return the current row being rendered.
     * tThis value should take any offest into account.
     *
     * @return int
     */
    public function getRowId()
    {
        return $this->rowId;
    }



    /**
     * init the filter form.
     *
     * You may need to add the filter submit/clear events here.
     * This is highly dependant on the type of renderer you are using
     * So it is left for you to implement for that reason.
     *
     * @return mixed
     */
    abstract protected function initFilterForm();

    /**
     * Render the table header
     *
     * @return mixed
     */
    abstract protected function showHeader();

    /**
     * Render the table body
     *
     * @return mixed
     */
    abstract protected function showBody();

    /**
     * Render the table row
     *
     * @param mixed $obj
     * @return mixed
     */
    abstract protected function showRow($obj);

    /**
     * Render the table cell
     *
     * @param Cell\Iface $cell
     * @param mixed $obj
     * @return mixed
     */
    abstract protected function showCell(Cell\Iface $cell, $obj);

    /**
     * Render the table footer
     *
     * @return mixed
     */
    abstract protected function showFooter();





}