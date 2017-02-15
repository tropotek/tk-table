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


    /**
     * All classes appended to this table
     * @var array
     */
    //protected $cssList = array();

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
     * @var array|\ArrayAccess
     */
    protected $request = null;

    /**
     * @var array|\ArrayAccess
     */
    protected $session = null;

    /**
     * @var string
     */
    protected $fixedOrderBy = null;



    /**
     * Create a table object
     *
     * @param string $id
     * 
     * @param array $params
     * @param array|\ArrayAccess $request
     * @param array|\ArrayAccess $session
     */
    public function __construct($id, $params = array(), $request = null, $session = null)
    {
        $this->id = $id;
        $this->paramList = $params;

        if (!$request) {
            $request = &$_REQUEST;
        }
        $this->request = &$request;

        if (!$session) {
            $session = &$_SESSION;
        }
        $this->session = &$session;

        $this->form = new Form($id.'Filter', $request);
        $this->form->setParamList($params);
        $this->form->addCss('form-inline');
        $this->setAttr('id', $this->getId());

    }

    /**
     * Execute the table
     * Generally called in the renderer`s show() method
     *
     */
    public function execute()
    {
        static $run = false;
        if (!$run) {
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
            $run = true;
        }
    }


    protected function initFilterForm()
    {
        // Add Filter button events
        $this->addFilter(new Event\Button($this->makeInstanceKey('search'), array($this, 'doSearch')))->setAttr('value', $this->makeInstanceKey('search'))->addCss('btn-primary')->setLabel('Search');
        $this->addFilter(new Event\Button($this->makeInstanceKey('clear'), array($this, 'doClear')))->setAttr('value', $this->makeInstanceKey('clear'))->setLabel('Clear');
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
     * Get the data list array
     *
     * @return array
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
     * This should be called from the clear filter event usually
     *
     * @return $this
     */
    public function clearFilterSession()
    {
        unset($this->session[$this->form->getId()]);
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
        $this->session[$this->form->getId()] = $this->form->getValues();
        return $this;
    }

    /**
     * Return the session array for the filter form
     *
     * @return array|mixed
     */
    public function getFilterSession()
    {
        if (isset($this->session[$this->form->getId()])) {
            return $this->session[$this->form->getId()];
        }
        return array();
    }

    /**
     * @return string
     */
    public function getFixedOrderBy()
    {
        return $this->fixedOrderBy;
    }

    /**
     * @param string $fixedOrderBy
     * @return $this
     */
    public function setFixedOrderBy($fixedOrderBy)
    {
        $this->fixedOrderBy = $fixedOrderBy;
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
        if (isset($this->session[$this->makeInstanceKey('dbTool')][$this->makeInstanceKey(Tool::PARAM_OFFSET)])) {
            $this->session[$this->makeInstanceKey('dbTool')][$this->makeInstanceKey(Tool::PARAM_OFFSET)] = 0;
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
        if (isset($this->session[$this->makeInstanceKey('dbTool')])) {
            $this->session[$this->makeInstanceKey('dbTool')] = 0;
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

        if (isset($this->session[$this->makeInstanceKey($key)])) {
            $tool->updateFromArray($this->session[$this->makeInstanceKey($key)]);
        }
        //$isRequest = $tool->updateFromArray($this->request);
        $isRequest = $tool->updateFromArray(\Tk\Uri::create()->all());  // Use GET params only
        if ($this->getFixedOrderBy() !== null) {
            $tool->setOrderBy($this->getFixedOrderBy());
        }

        if ($isRequest) {   // note, should only fire on GET requests.
            $this->session[$this->makeInstanceKey($key)] = $tool->toArray();
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






//
//    /**
//     * Add a cell css class
//     *
//     * @param string $class
//     * @return $this
//     */
//    public function addCss($class)
//    {
//        $this->cssList[$class] = $class;
//        return $this;
//    }
//
//    /**
//     * remove a css class
//     *
//     * @param string $class
//     * @return $this
//     */
//    public function removeCss($class)
//    {
//        unset($this->cssList[$class]);
//        return $this;
//    }
//
//    /**
//     * Get the css class list
//     *
//     * @return array
//     */
//    public function getCssList()
//    {
//        return $this->cssList;
//    }
//
//    /**
//     * Set the css cell class list
//     * If no parameter sent the array is cleared.
//     *
//     * @param array $arr
//     * @return $this
//     */
//    public function setCssList($arr = array())
//    {
//        $this->cssList = $arr;
//        return $this;
//    }

    
}