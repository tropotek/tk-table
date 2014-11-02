<?php

/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */

namespace Table;

/**
 * The dynamic table Controller
 *
 *
 * @package Table
 */
class Table extends \Tk\Object
{

    const SID = 'Tbl-';

    /**
     * @var string
     */
    protected $id = '';

    /**
     * @var \Table\Cell\Interface[]
     */
    protected $cellList = array();

    /**
     * @var \Table\Action\Interface[]
     */
    protected $actionList = array();

    /**
     * @var \Form\Field\Interface[]
     */
    protected $filterList = array();

    /**
     * @var \Form\Form
     */
    protected $form = null;

    /**
     * @var \Tk\Db\Tool
     */
    protected $dbTool = null;

    /**
     * @var \Tk\Db\Array|array
     */
    protected $list = null;
    protected $inited = null;
    protected $executed = null;


    public $lastSqlQuery = '';


    /**
     * __construct
     *
     */
    public function __construct($id = '')
    {
        if (!$id) {
            $id = $this->getUri()->getBasename(false);
        }
        $this->id = $id;

        $this->form = \Form\Factory::getInstance()->createForm('Table_Form_' . $this->getInstanceId());
        $this->form->deleteCssClass('form-horizontal');
        $this->form->addCssClass('form-inline');
        $this->form->setAction($this->getUri());
        $this->form->setInstanceId($this->getInstanceId());
        //$this->form->setInline(true);
        $this->form->addCssClass('form-inline');
    }

    /**
     * Get the table Id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Called by Table renderer
     *
     */
    public function initFilters()
    {
        if (count($this->getFilterList())) {
            $this->getForm()->attach(new Event\Search('search', $this), 'search');
            $this->getForm()->attach(new Event\Clear($this), 'clear');
        }
    }

    /**
     * Get a unique session name for this table
     *
     * @return string
     */
    public function getSessionHash()
    {
        return self::SID . md5($this->getUri()->getPath(true) . '-' . $this->getInstanceId());
    }

    /**
     * This method only needs to be called if you have any
     * filters in the tables form.
     *
     *
     */
    public function init()
    {
        tklog('Table::init("'.$this->getId().'")');
        if ($this->inited)
            return;
        $this->inited = true;

        $this->initFilters();

        $ses = $this->getSession();
        $sesId = $this->getSessionHash();

        if (!$this->getForm()->isSubmitted()) {
            if ($ses->exists($sesId)) {
                $values = $ses->get($sesId);
                $this->form->loadFromArray($values);
            }
        }
    }

    /**
     * Execute the command
     * The list is set generally after execute() is called
     *
     */
    public function execute()
    {
        tklog('Table::execute("'.$this->getId().'")');
        if ($this->executed)
            return;

        $this->executed = true;
        
        /* @var $action \Table\Action\Interface */
        foreach ($this->getActionList() as $action) {
            if ($this->getRequest()->exists($action->getObjectKey($action->getEvent()))) {
                $action->execute($this->getList());
            }
        }

        // Execute Cell objects

        /* @var $cell \Table\Cell\Interface */
        foreach ($this->getCellList() as $cell) {
            $cell->execute($this->getList());
        }
        $this->getForm()->execute();
    }

    /**
     * Create a DbTool from the request using the table ID and
     * default parameters...
     *
     * @param string $defaultSort
     * @param int $defaultLimit
     * @return \Tk\Db\Tool
     */
    public function getDbTool($defaultSort = '', $defaultLimit = 50)
    {
        if (!$this->dbTool) {
            $this->dbTool = \Table\Db\Tool::createFromRequest($this->getInstanceId());
        }

        if ($defaultSort) {
            $this->dbTool->setOrderBy($defaultSort);
        }
        if ($defaultLimit) {
            $this->dbTool->setLimit($defaultLimit);
        }
        return $this->dbTool;
    }

    /**
     *
     * @param bool $b
     * @return \Table\Table
     * @deprecated No longer used... Remove in Ver 2.0.0 of lib
     */
    public function resetOffset($b = true)
    {
        return $this;
    }


    /**
     * Set the list
     *
     * @param \Tk\Db\ArrayObject|array $list
     * @return Table
     * @todo Fix this: Bug, caused by double executing the sql query when a filter search is performed
     *       because the module->doDefault() method is called upon submit then the form redirects executing the
     *       sql again. The first sql execution must be stopped. We could create a getList() callback that
     *       gets attached to the parent module say doGetList() in the main calling module. then the table
     *       has control of its execution and it can be ignored on form submission. Workaround for now is
     *       to use:
     * <code>
     * <?php
     *   if (!$this->table->getForm()->isSubmitted()) {  // to avoid duplicate sql execution
     *     $list = $this->findFiltered($filter, $this->table->getDbTool());
     *     $this->table->setList($list);
     *   }
     * ?>
     * </code>
     */
    public function setList($list)
    {
        $this->lastSqlQuery = $this->getConfig()->getDb()->getLastQuery();
        $this->list = $list;
        return $this;
    }

    /**
     * Get the list
     *
     * @return \Tk\Db\ArrayObject
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * Get all the request key/value pairs for the filters
     *
     * @return array
     */
    public function getFilterValues()
    {
        $array = array();
        /* @var $filter \Form\Field\Iface */
        foreach ($this->filterList as $filter) {
            $array[$filter->getName()] = $filter->getValue();
        }
        return $array;
    }

    /**
     * Get this table's form object
     *
     * @return \Form\Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Add an action to this table
     *
     * @param Action\Iface $action
     * @return Action\Iface
     */
    public function addAction($action)
    {
        $action->setTable($this);
        $this->actionList[] = $action;
        return $action;
    }

    /**
     * Get the action list array
     *
     * @return array
     */
    public function getActionList()
    {
        return $this->actionList;
    }

    /**
     * Add a filter to this table
     *
     * @param \Form\Field\Iface $filter
     * @return \Form\Field\Iface
     */
    public function addFilter($filter)
    {
        $filter->addCssClass('input-sm');
        $this->filterList[] = $filter;
        $this->getForm()->addField($filter);
        return $filter;
    }

    /**
     * Get the filter list array
     * Contains \Form\Field\Iface objects
     *
     * @return array
     */
    public function getFilterList()
    {
        return $this->filterList;
    }

    /**
     * Add a cell to this table
     *
     * @param Cell\Iface $cell
     * @return Cell\Iface
     */
    public function addCell($cell)
    {
        $cell->setTable($this);
        $this->cellList[] = $cell;
        return $cell;
    }

    /**
     * Set the cells, init with the table
     *
     * @param Cell\Iface[] $array
     * @return Table
     */
    public function setCells($array)
    {
        foreach ($array as $cell) {
            $cell->setTable($this);
        }
        $this->cellList = $array;
        return $this;
    }

    /**
     * Get a cell from the array by its property name
     *
     * @param string $property
     * @return \Table\Cell\Interface
     */
    public function getCell($property)
    {
        if (array_key_exists($property, $this->cellList)) {
            return $this->cellList[$property];
        }
    }

    /**
     * Get the cell list array
     *
     * @return array
     */
    public function getCellList()
    {
        return $this->cellList;
    }

    /**
     * Get the HTML table ID string
     *
     * @return string
     */
    public function getTableId()
    {
        return 'Table_' . $this->getId();
    }

}
