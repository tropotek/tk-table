<?php
namespace Tk;

use Tk\Table\Action;
use Tk\Table\Cell;
use Tk\Db\Tool;
use \Tk\Form\Event;

/**
 * Class Table
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 * TODO: Thinking of moving the filter form and actions out to their own objects so we
 * TODO: can remove the responsibility from the Table ????
 * TODO: Then I think we can remove the need for a session and request from the Table Object ?? ;-)
 */
class Table implements \Tk\InstanceKey
{

    const PARAM_ORDER_BY = 'orderBy';
    const ORDER_NONE = '';
    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';

    use \Tk\Dom\AttributesTrait;
    use \Tk\Dom\CssTrait;

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
     * @var array|\ArrayAccess
     */
    protected $paramList = array();

    /**
     * @var array
     */
    protected $list = null;

    /**
     * @var Form
     */
    protected $form = null;

    /**
     * @var \Tk\Request|array|\ArrayAccess
     */
    protected $request = null;

    /**
     * @var \Tk\Session|array|\ArrayAccess
     */
    protected $session = null;

    /**
     * @var string
     */
    protected $staticOrderBy = null;

    /**
     * @var bool
     */
    protected $hasExecuted = false;

    /**
     * @var null|\Tk\Event\Dispatcher
     */
    protected $dispatcher = null;


    /**
     * Create a table object
     *
     * @param string $tableId
     * @param array $params
     */
    public function __construct($tableId, $params = array())
    {
        $this->id = $tableId;
        $this->paramList = $params;
        $this->setAttr('id', $this->getId());

        if (!$this->request) {
            $this->request = &$_REQUEST;
        }
        if (!$this->session) {
            $this->session = &$_SESSION;
        }
        $this->form = $this->makeForm();
    }

    /**
     * @return Form
     */
    protected function makeForm()
    {
        $form = new Form($this->id . 'Filter');
        $form->setParamList($this->paramList);
        $form->addCss('form-inline');
        return $form;
    }

    /**
     * @param $id
     * @param array $params
     * @param null|array|\Tk\Request $request
     * @param null|array|\Tk\Session $session
     * @return static
     */
    public static function create($id, $params = array(), $request = null, $session = null)
    {
        $obj = new static($id, $params);
        if (!$request)
            $request = \Tk\Config::getInstance()->getRequest();
        if (!$session)
            $session = \Tk\Config::getInstance()->getSession();
            
        $obj->setRequest($request);
        $obj->setSession($session);
        
        return $obj;
    }

    /**
     * Execute the table
     * Generally called in the renderer`s show() method
     *
     */
    public function execute()
    {
        if (!$this->hasExecuted) {
            /* @var Cell\Iface $cell */
            foreach ($this->getCellList() as $cell) {
                $cell->execute();
            }
            /* @var Action\Iface $action */
            foreach ($this->getActionList() as $action) {
                $action->init();
                if ($action->hasTriggered()) {
                    $action->execute();
                }
            }
            $this->hasExecuted = true;
        }
    }


    protected function initFilterForm()
    {
        // Add Filter button events
        $this->addFilter(new Event\Submit($this->makeInstanceKey('search'), array($this, 'doSearch')))->setAttr('value', $this->makeInstanceKey('search'))->addCss('btn-primary')->setLabel('Search');
        $this->addFilter(new Event\Submit($this->makeInstanceKey('clear'), array($this, 'doClear')))->setAttr('value', $this->makeInstanceKey('clear'))->setLabel('Clear');
    }

    public function doSearch($form)
    {
        //  Save to session
        $this->saveFilterSession();
        $this->resetSessionOffset();
        $this->getUri($form)->redirect();
    }

    public function doClear($form)
    {
        // Clear session
        $this->clearFilterSession();
        $this->resetSessionOffset();
        $this->getUri($form)->redirect();
    }

    /**
     * @param Form $form
     * @return Uri
     */
    protected function getUri($form = null)
    {
        $uri = \Tk\Uri::create();
        if ($form) {
            /* @var \Tk\Form\Field\Iface $field */
            foreach ($form->getFieldList() as $field) {
                $uri->remove($field->getName());
            }
        }
        return $uri;
    }

    /**
     * @param \Tk\Request|array|\ArrayAccess $request
     * @return $this
     */
    public function setRequest(&$request)
    {
        $this->request = &$request;
        return $this;
    }
    
    /**
     * @return array|\ArrayAccess
     */
    public function &getRequest()
    {
        return $this->request;
    }

    /**
     * @param \Tk\Session|array|\ArrayAccess $session
     * @return $this
     */
    public function setSession(&$session)
    {
        $this->session = &$session;
        return $this;
    }
    
    /**
     * @return array|\ArrayAccess
     */
    public function &getSession()
    {
        return $this->session;
    }

    /**
     * Get the data list array
     *
     * @return array|\Tk\Db\Map\ArrayObject
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * @param array|\ArrayAccess|\Iterator $list
     * @return $this
     */
    public function setList($list)
    {
        $this->list = $list;
        $this->execute();
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
     * @return string|mixed
     */
    public function getParam($name)
    {
        if (!empty($this->paramList[$name])) {
            return $this->paramList[$name];
        }
        return '';
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setParam($name, $value)
    {
        $this->paramList[$name] = $value;
        return $this;
    }

    /**
     * Get the param array
     *
     * @return array
     */
    public function getParamList()
    {
        return $this->paramList;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function setParamList($params)
    {
        $this->paramList = $params;
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
     * Add a field element before another element
     *
     * @param Cell\Iface $anchorCell
     * @param Cell\Iface $cell
     * @return Cell\Iface
     */
    public function addCellBefore($anchorCell, $cell)
    {
        $newArr = array();
        $cell->setTable($this);
        /** @var Cell\Iface $c */
        foreach ($this->getCellList() as $c) {
            if ($c === $anchorCell) {
                $newArr[] = $cell;
            }
            $newArr[] = $c;
        }
        $this->setCellList($newArr);
        return $cell;
    }

    /**
     * Add an element after another element
     *
     * @param Cell\Iface $anchorCell
     * @param Cell\Iface $cell
     * @return Cell\Iface
     */
    public function addCellAfter($anchorCell, $cell)
    {
        $newArr = array();
        $cell->setTable($this);
        /** @var Cell\Iface $c */
        foreach ($this->getCellList() as $c) {
            $newArr[] = $c;
            if ($c === $anchorCell) {
                $newArr[] = $cell;
            }
        }
        $this->setCellList($newArr);
        return $cell;
    }

    /**
     * Remove a cell from the table
     *
     * @param Cell\Iface $cell
     * @return $this
     */
    public function removeCell($cell)
    {
        /** @var Cell\Iface $c */
        foreach ($this->getCellList() as $i => $c) {
            if ($c === $cell) {
                unset($this->cellList[$i]);
                $this->cellList = array_values($this->cellList);
                break;
            }
        }
        return $this;
    }

    /**
     * Find a cell in the table that match the given property and/or label
     *
     * @param string $property
     * @param null|string $label
     * @return array|Cell\Iface
     */
    public function findCell($property, $label = null)
    {
        $found = $this->findCells($property, $label);
        return current($found);
    }

    /**
     * Find all cells that match the given property and/or label
     *
     * @param string $property
     * @param null|string $label
     * @return array
     */
    public function findCells($property, $label = null)
    {
        $found = array();
        foreach ($this->getCellList() as $c) {
            if ($c->getProperty() == $property) {
                if ($label !== null) {
                    if ($c->getLabel() == $label)
                        $found[] = $c;
                } else {
                    $found[] = $c;
                }
            }
        }
        return $found;
    }

    /**
     * Set the cells, init with the table
     *
     * @param Cell\Iface[] $array
     * @return Table
     */
    public function setCellList($array)
    {
        foreach ($array as $cell) {
            $cell->setTable($this);
        }
        $this->cellList = $array;
        return $this;
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
        $this->actionList[$action->getName()] = $action;
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
     * @param \Tk\Form\Field\Iface $field
     * @return \Tk\Form\Field\Iface
     */
    public function addFilter($field)
    {
        if (!$field instanceof \Tk\Form\Event\Iface && !count($this->getFilterForm()->getFieldList())) {
            $this->initFilterForm();
        }
        $field->setLabel(null);
        return $this->getFilterForm()->addField($field);
    }

    /**
     * getFilterValues
     *
     * @param null|array|string $regex A regular expression or array of field names to get
     * @return array
     */
    public function getFilterValues($regex = null)
    {
        static $x = false;
        if (!$x && $this->getFilterForm()) { // execute form on first access
            $this->getFilterForm()->load($this->getFilterSession());
            $this->getFilterForm()->execute($this->getRequest());
            $x = true;
        }
        return $this->getFilterForm()->getValues($regex);
    }

    /**
     * Clear the filter form session data.
     * This should be called from the clear filter event usually
     *
     * @return $this
     */
    public function clearFilterSession()
    {
        $session = $this->getSession();
        if ($session && $this->getFilterForm()) {
            unset($session[$this->getFilterForm()->getId()]);
        }
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
        $session = $this->getSession();
        if ($session && $this->getFilterForm()) {
            $session[$this->getFilterForm()->getId()] = $this->getFilterForm()->getValues();
        }
        return $this;
    }

    /**
     * Return the session array for the filter form
     *
     * @return array|mixed
     */
    public function getFilterSession()
    {
        $session = $this->getSession();
        if ($session && $this->getFilterForm() && isset($session[$this->getFilterForm()->getId()])) {
            return $session[$this->getFilterForm()->getId()];
        }
        return array();
    }

    /**
     * @return string
     */
    public function getStaticOrderBy()
    {
        return $this->staticOrderBy;
    }

    /**
     * @param string $staticOrderBy
     * @return $this
     */
    public function setStaticOrderBy($staticOrderBy)
    {
        $this->staticOrderBy = $staticOrderBy;
        return $this;
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
        if (count($ord) >= 2) {
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
//        if (preg_match('/(\S+) (ASC|DESC)$/i', $this->makeDbTool()->getOrderBy(), $regs)) {
//            return array(trim($regs[1]), $regs[2]);
//        }
        if ($this->getList() instanceof \Tk\Db\Map\ArrayObject) {
            return explode(' ', $this->makeDbTool()->getOrderBy());
        }
        return array();
    }

    /**
     * Reset the db tool offset to 0
     *
     * @return $this
     */
    public function resetSessionOffset()
    {
        $session = $this->getSession();
        if ($session && isset($session[$this->makeInstanceKey('dbTool')])) {
            $instKey = $session[$this->makeInstanceKey('dbTool')];
            if (isset($instKey[$this->makeInstanceKey(Tool::PARAM_OFFSET)])) {
                $instKey[$this->makeInstanceKey(Tool::PARAM_OFFSET)] = 0;
                $session[$this->makeInstanceKey('dbTool')] = $instKey;
            }
        }
        return $this;
    }

    /**
     * Reset the db tool offset to 0
     *
     * @return $this
     */
    public function resetSessionTool()
    {
        $session = $this->getSession();
        if ($session && isset($session[$this->makeInstanceKey('dbTool')])) {
            //$session[$this->makeInstanceKey('dbTool')] = 0;
            unset($session[$this->makeInstanceKey('dbTool')]);
        }
        return $this;
    }

    /**
     * Create a DbTool from the request using the table ID and
     * default parameters...
     *
     * @param string $defaultOrderBy
     * @param int $defaultLimit
     * @return Tool
     * TODO: we could put this into the pager`s area of responsibility if we wish to reduce the Table objects complexity
     */
    public function makeDbTool($defaultOrderBy = '', $defaultLimit = 25)
    {
        $tool = Tool::create($defaultOrderBy, $defaultLimit);
        $tool->setInstanceId($this->getId());
        $key = 'dbTool';
        $session = $this->getSession();

        if ($session && isset($session[$this->makeInstanceKey($key)])) {
            $tool->updateFromArray($session[$this->makeInstanceKey($key)]);
        }
        //$isRequest = $tool->updateFromArray($this->request);
        $isRequest = $tool->updateFromArray(\Tk\Uri::create()->all());  // Use GET params only
        if ($this->getStaticOrderBy() !== null) {
            $tool->setOrderBy($this->getStaticOrderBy());
        }

        if ($isRequest) {   // note, should only fire on GET requests.
            $session[$this->makeInstanceKey($key)] = $tool->toArray();
            \Tk\Uri::create()
                ->remove($this->makeInstanceKey(Tool::PARAM_ORDER_BY))
                ->remove($this->makeInstanceKey(Tool::PARAM_LIMIT))
                ->remove($this->makeInstanceKey(Tool::PARAM_OFFSET))
                ->remove($this->makeInstanceKey(Tool::PARAM_GROUP_BY))
                ->remove($this->makeInstanceKey(Tool::PARAM_HAVING))
                ->remove($this->makeInstanceKey(Tool::PARAM_DISTINCT))
                ->redirect();
        }
        return $tool;
    }

    /**
     * Create request keys with prepended string
     *
     * returns: `{instanceId}_{$key}`
     *
     * @param $key
     * @return string
     */
    public function makeInstanceKey($key)
    {
        return $this->getId() . '_' . $key;
    }


    /**
     * @return null|\Tk\Event\Dispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @param null|\Tk\Event\Dispatcher $dispatcher
     */
    public function setDispatcher($dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
    
}