<?php
namespace Tk\Table\Action;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class ColumnSelect extends Button
{

    /**
     * @var array
     */
    protected $disabled = array();

    /**
     * @var array
     */
    protected $selected = array();

    /**
     * @var array
     */
    protected $unselected = array();


    /**
     * @param string $name
     * @param string $icon
     * @param null $url
     */
    public function __construct($name = 'columns', $icon = 'fa fa-list-alt', $url = null)
    {
        parent::__construct($name, $icon, $url);
        $this->setAttr('type', 'button');
        $this->addCss('tk-column-select-btn');
        $request = \Tk\Config::getInstance()->getRequest();
        if ($request->has('action') && preg_match('/^session\.([a-z]+)/', $request->get('action'), $regs)) {
            $this->doAction($request, $regs[1]);
        }
    }

    /**
     * @param string $name
     * @param string $icon
     * @param null $url
     * @return ColumnSelect
     */
    static function create($name = 'columns', $icon = 'fa fa-columns', $url = null)
    {
        return new static($name, $icon, $url);
    }

    /**
     * @return string
     */
    public function getSid()
    {
        return $this->getTable()->getId().'-'.$this->getTable()->getInstanceId();
    }

    /**
     * @param \Tk\Request $request
     * @param string $action
     */
    public function doAction(\Tk\Request $request, $action)
    {
        $session = \Tk\Config::getInstance()->getSession();
        $data = array();
        try {
            switch ($action) {
                case 'set':
                    if (!$request->get('name') || !$request->get('value'))
                        throw new \Tk\Exception('Invalid parameter name or value');
                    $session->set($request->get('name'), $request->get('value'));
                    $data['name'] = $request->get('name');
                    $data['value'] = $request->get('value');
                    break;
                case 'get':
                    if (!$request->get('name'))
                        throw new \Tk\Exception('Invalid parameter name');
                    $data['name'] = $request->get('name');
                    $data['value'] = $session->get($request->get('name'));
                    break;
                case 'remove':
                    if (!$request->get('name'))
                        throw new \Tk\Exception('Invalid parameter name');
                    $data['name'] = $request->get('name');
                    $data['value'] = $session->get($request->get('name'));
                    $session->remove($request->get('name'));
                    break;
            }
            $response = \Tk\ResponseJson::createJson($data);
            $response->send();
        } catch (\Exception $e) {
            $response = \Tk\ResponseJson::createJson($data, \Tk\Response::HTTP_INTERNAL_SERVER_ERROR);
            $response->send();
        }
        exit();
    }


    /**
     * Setup the disabled columns using their property name
     * This stops the user from hiding the column
     *
     * EG:
     *   array('cb_id', 'name');
     *
     * @param $arr
     * @return $this
     */
    public function setDisabled($arr)
    {
        $this->disabled = $arr;
        return $this;
    }

    /**
     * Setup the disabled columns using their property name
     *
     * @param $selector
     * @return $this
     */
    public function addDisabled($selector)
    {
        $this->disabled[$selector] = $selector;
        return $this;
    }

    /**
     * @param $selector
     * @return $this
     */
    public function removeDisabled($selector)
    {
        if(isset($this->disabled[$selector])) {
            unset($this->disabled[$selector]);
        }
        return $this;
    }


    /**
     * Setup the default shown columns using their property name
     *
     * EG:
     *   array('cb_id', 'name', 'username', 'email', 'role', 'active', 'created');
     *
     * @param $arr
     * @return $this
     */
    public function setSelected($arr)
    {
        if(!empty($arr[0])) $arr = array_combine($arr, $arr);
        $this->selected = $arr;
        return $this;
    }

    /**
     * Setup the default shown columns using their property name
     *
     * @param $selector
     * @return $this
     */
    public function addSelected($selector)
    {
        $this->selected[$selector] = $selector;
        return $this;
    }

    /**
     * @param $selector
     * @return $this
     */
    public function removeSelected($selector)
    {
        if(isset($this->selected[$selector])) {
            unset($this->selected[$selector]);
        }
        return $this;
    }


    /**
     * Setup the default hidden columns using their property name
     *
     * EG:
     *   array('cb_id', 'name', 'username', 'email', 'role', 'active', 'created');
     *
     * @param $arr
     * @return $this
     * @todo: We need a setDefaultSelected(array) method instead of this one, think of it for the future
     * @todo:  If there is no default then all should show by default.
     */
    public function setUnselected($arr)
    {
        if(!empty($arr[0])) $arr = array_combine($arr, $arr);
        $this->unselected = $arr;
        return $this;
    }

    /**
     * Setup the default hidden columns using their property name
     *
     * @param $selector
     * @return $this
     */
    public function addUnselected($selector)
    {
        $this->unselected[$selector] = $selector;
        return $this;
    }

    /**
     * remove the default hidden columns using their property name
     *
     * @param $selector
     * @return $this
     */
    public function removeUnselected($selector)
    {
        if(isset($this->unselected[$selector])) {
            unset($this->unselected[$selector]);
        }
        return $this;
    }

    /**
     * Reset the cookies for this module
     *
     * @param bool $b
     * @return $this
     */
    public function reset($b = true)
    {
        if ($b) {
            \Tk\Config::getInstance()->getSession()->remove($this->getSid());
        }
        return $this;
    }


    /**
     * Use this method to convert a property array to an array
     * of column numbers for the column select plugin
     *
     * @param $arr
     * @return array
     */
    private function propsToCols($arr) {
        $nums = array();
        $i = 0;
        /** @var \Tk\Table\Cell\Iface $cell */
        foreach ($this->getTable()->getCellList() as $k => $cell) {
            if (in_array($cell->getProperty(), $arr)) {
                $nums[] = $i;   // int not string
            }
            $i++;
        }
        return $nums;
    }

    /**
     * @return mixed|void
     */
    public function execute()
    {
        parent::execute();
    }

    /**
     * @return string|\Dom\Template
     */
    public function show()
    {
        $disabledStr = implode(', ', $this->propsToCols($this->disabled));
        $selectedStr =  implode(', ', $this->propsToCols($this->selected));
        $unselectedStr =  implode(', ', $this->propsToCols($this->unselected));
        $this->setAttr('data-sid', $this->getSid());
        $this->setAttr('data-button-id', $this->getTable()->makeInstanceKey($this->getName()));
        $this->setAttr('data-disabled', '['.$disabledStr.']');
        $this->setAttr('data-default-selected', '['.$selectedStr.']');
        $this->setAttr('data-default-unselected', '['.$unselectedStr.']');

        $template = parent::show();

        $template->appendJsUrl(\Tk\Uri::create('/vendor/ttek/tk-table/js/jquery.columnSelect.js'));

        $js = <<<JS
jQuery(function ($) {
  
  $('.tk-column-select-btn').columnSelect({});
  
});
JS;
        $template->appendJs($js);

        return $template;
    }

}
