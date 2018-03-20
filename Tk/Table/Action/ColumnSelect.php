<?php
namespace Tk\Table\Action;

use \Tk\Table\Cell;

/**
 *
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class ColumnSelect extends Button
{


    protected $disabled = array();

    protected $selected = array();

    protected $unselected = array();

    protected $resetColumns = false;


    /**
     * Create
     *
     * @param string $name
     * @param string $icon
     * @param null $url
     * @throws \Tk\Exception
     */
    public function __construct($name = 'columns', $icon = 'glyphicon glyphicon-list-alt', $url = null)
    {
        parent::__construct($name, $icon, $url);
        $this->addCss('tk-action-column-select');
    }

    /**
     * Create
     *
     * @param string $name
     * @param string $icon
     * @param null $url
     * @return ColumnSelect
     * @throws \Tk\Exception
     */
    static function create($name = 'columns', $icon = 'fa fa-columns', $url = null)
    {
        return new static($name, $icon, $url);
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
        $this->resetColumns = $b;
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
     * @return string|\Dom\Template
     * @throws \Dom\Exception
     */
    public function show()
    {
        $template = parent::show();

        $btnId = $this->getTable()->makeInstanceKey($this->getName());
        $tableId = $this->getTable()->getId();

        $template->appendJsUrl(\Tk\Uri::create('/vendor/ttek/tk-table/js/js.cookie.js'));
        $template->appendJsUrl(\Tk\Uri::create('/vendor/ttek/tk-table/js/jquery.columnSelect.js'));

        $disabledStr = implode(', ', $this->propsToCols($this->disabled));
        $selectedStr =  implode(', ', $this->propsToCols($this->selected));
        $unselectedStr =  implode(', ', $this->propsToCols($this->unselected));
        $resetColumns = ($this->resetColumns) ? 'true' : 'false';

        $js = <<<JS
jQuery(function ($) {
  //$('#$tableId').
  $('.tk-action-column-select').closest('.tk-table').columnSelect({
    buttonId : '$btnId',
    disabled : [$disabledStr],
    disabledHidden : false,
    defaultSelected : [$selectedStr],
    defaultUnselected : [$unselectedStr],
    resetCookies: $resetColumns
  });
});
JS;
        $template->appendJs($js);

        return $template;
    }

}
