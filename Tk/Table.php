<?php
namespace Tk;

use Tk\Table\Action;
use Tk\Table\Cell;
use Tk\Form;
use Tk\Form\Field;

/**
 * Class Table
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Table
{
    use \Tk\InstanceTrait;

    const ORDER_NONE = '';
    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';


    /**
     * @var string
     */
    protected $id = '';

    /**
     * @var Action\Iface[]
     */
    protected $actionList = array();

    /**
     * @var Cell\Iface[]
     */
    protected $cellList = array();

    /**
     * @var array
     */
    protected $params = null;

    /**
     * @var array
     */
    protected $list = null;

    /**
     * @var Form
     */
    protected $form = null;

    /**
     * @var array|\ArrayAccess
     */
    protected $request = null;

    /**
     * @var array|\ArrayAccess
     */
    protected $session = null;



    /**
     * Create a table object
     *
     * @param string $id
     * @param array $params
     * @param array|\ArrayAccess $request
     * @param array|\ArrayAccess $session
     */
    public function __construct($id, $params = array(), $request = null, $session = null)
    {
        $this->id = $id;
        $this->setInstanceId($id);
        $this->params = $params;

        if (!$request) {
            $request = &$_REQUEST;
        }
        $this->request = &$request;

        if (!$session) {
            $session = &$_SESSION;
        }
        $this->session = &$session;

        $this->form = new Form($id.'Filter', $params, $request);
        $this->form->setAttr('action', \Tk\Url::create());
        $this->form->addCss('form-inline');

        //Clear the DB tool session. When testing only....
        //unset($this->session[$this->makeInstanceKey('dbTool')]);

    }

    /**
     * Execute the table
     *
     */
    public function execute()
    {
        /** @var Action\Iface $action */
        foreach($this->getActionList() as $action) {
            if (!$action instanceof Action\Iface) continue;
            $action->init();
            if ($action->hasFired()) {
                $action->execute();   
            }
        }
    }

    /**
     * @return array|\ArrayAccess
     */
    public function &getRequest()
    {
        return $this->request;
    }

    /**
     * @return array|\ArrayAccess
     */
    public function &getSession()
    {
        return $this->session;
    }

    /**
     *
     * @return Form
     */
    public function getFilterForm()
    {
        return $this->form;
    }

    /**
     * Add a field to the filter form
     *
     * @param \Tk\Form\Element $field
     * @return \Tk\Form\Element
     */
    public function addFilter($field)
    {
        return $this->getFilterForm()->addField($field);
    }

    /**
     * getFilterValues
     *
     */
    public function getFilterValues()
    {
        static $x = false;
        if (!$x) { // execute form on first access
            $this->form->load($this->getFilterSession());
            $this->getFilterForm()->execute();
        }
        return $this->getFilterForm()->getValues();
    }

    /**
     * Clear the filter form session data.
     * This should be called from teh clear filter event usually
     *
     * @return $this
     */
    public function clearFilterSession()
    {
        unset($this->session[$this->form->getInstanceId()]);
        return $this;
    }

    /**
     * Save the filter form session data
     * This should be called from the search filter event
     *
     * @return $this
     */
    public function saveFilterSession()
    {
        $this->session[$this->form->getInstanceId()] = $this->form->getValues();
        return $this;
    }

    /**
     * Return the session array for the filter form
     *
     * @return array|mixed
     */
    public function getFilterSession()
    {
        if (isset($this->session[$this->form->getInstanceId()])) {
            return $this->session[$this->form->getInstanceId()];
        }
        return array();
    }

    /**
     * Reset the db tool offset to 0
     *
     * @return $this
     */
    public function resetOffsetSession()
    {
        if (isset($this->session[$this->makeInstanceKey('dbTool')][$this->makeInstanceKey(\Tk\Db\Mapper::PARAM_OFFSET)])) {
            $this->session[$this->makeInstanceKey('dbTool')][$this->makeInstanceKey(\Tk\Db\Mapper::PARAM_OFFSET)] = 0;
        }
        return $this;
    }

    /**
     * Get the data list array
     *
     * @return array|\Tk\Db\ArrayObject
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * @param array|\Tk\Db\ArrayObject $list
     * @return $this
     */
    public function setList($list)
    {
        $this->list = $list;
        return $this;
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
     * Get a parameter from the array
     *
     * @param $name
     * @return bool
     */
    public function getParam($name)
    {
        if (!empty($this->params[$name])) {
            return $this->params[$name];
        }
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function addParam($name, $value)
    {
        $this->params[$name] = $value;
        return $this;
    }

    /**
     * Get the param array
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
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
     * @return Cell\Iface
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
     * @return Cell\Iface[]
     */
    public function getCellList()
    {
        return $this->cellList;
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
        $action->setInstanceId($this->getId());
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
     * Get the active order By value
     *
     * Will be one of: '', 'ASC', 'DESC'
     *
     * @return string
     */
    public function getOrder()
    {
        $ord = $this->getOrderStatus();
        if (count($ord)) {
            return trim($ord[1]);
        }
        return self::ORDER_NONE;
    }

    /**
     * Get the active order property
     *
     * @return string
     */
    public function getOrderProperty()
    {
        $ord = $this->getOrderStatus();
        if (count($ord)) {
            return trim($ord[0]);
        }
        return '';
    }

    /**
     * Get the property and order value from the Request or params
     *
     * EG: from "lastName DESC" TO array('lastName', 'DESC');
     *
     * @return array
     */
    private function getOrderStatus()
    {
        if ($this->getList() instanceof \Tk\Db\ArrayObject) {
            return explode(' ', $this->getList()->getTool()->getOrderBy());
        }
        return array();
    }

    /**
     * Create a DbTool from the request using the table ID and
     * default parameters...
     *
     * @param string $defaultOrderBy
     * @param int $defaultLimit
     * @return \Tk\Db\Tool
     */
    public function makeDbTool($defaultOrderBy = '', $defaultLimit = 25)
    {
        $tool = \Tk\Db\Tool::create($defaultOrderBy, $defaultLimit)->setInstanceId($this->getInstanceId());
        $key = 'dbTool';

        if (isset($this->session[$this->makeInstanceKey($key)])) {
            $tool->updateFromArray($this->session[$this->makeInstanceKey($key)]);
        }

        if ($tool->updateFromArray($this->request)) {
            $this->session[$this->makeInstanceKey($key)] = $tool->toArray();
            \Tk\Url::create()
                ->delete($this->makeInstanceKey(\Tk\Db\Mapper::PARAM_ORDER_BY))
                ->delete($this->makeInstanceKey(\Tk\Db\Mapper::PARAM_LIMIT))
                ->delete($this->makeInstanceKey(\Tk\Db\Mapper::PARAM_OFFSET))
                ->delete($this->makeInstanceKey(\Tk\Db\Mapper::PARAM_GROUP_BY))
                ->delete($this->makeInstanceKey(\Tk\Db\Mapper::PARAM_HAVING))
                ->delete($this->makeInstanceKey(\Tk\Db\Mapper::PARAM_DISTINCT))
                ->redirect();
        }

        return $tool;
    }

}