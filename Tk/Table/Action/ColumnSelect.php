<?php
namespace Tk\Table\Action;

use \Tk\Table\Cell;

/**
 *
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class ColumnSelect extends Button
{


    protected $disabledSelectors = array();

    protected $defaultSelectors = array();



    /**
     * Create
     *
     * @param string $name
     * @param string $icon
     * @param null $url
     */
    public function __construct($name = 'columns', $icon = 'glyphicon glyphicon-list-alt', $url = null)
    {
        parent::__construct($name, $icon, $url);
    }

    /**
     * Create
     *
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
     * Setup the disabled columns using their property name
     *
     * EG:
     *   array('cb_id', 'name');
     *
     * @param $arr
     * @return $this
     */
    public function setDisabledSelectors($arr)
    {
        $this->disabledSelectors = $arr;
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
    public function setDefaultSelectors($arr)
    {
        $this->defaultSelectors = $arr;
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
     */
    public function getHtml()
    {
        $template = parent::getHtml();

        $btnId = $this->getTable()->makeInstanceKey($this->getName());
        $tableId = $this->getTable()->getId();

        $template->appendJsUrl(\Tk\Uri::create('/vendor/ttek/tk-table/js/js.cookie.js'));
        $template->appendJsUrl(\Tk\Uri::create('/vendor/ttek/tk-table/js/jquery.columnSelect.js'));

        $disablesStr = implode(', ', $this->propsToCols($this->disabledSelectors));
        $defaultStr =  implode(', ', $this->propsToCols($this->defaultSelectors));

        $js = <<<JS
jQuery(function ($) {
    
  $('#$tableId').columnSelect({
    buttonId : '$btnId',
    disabled : [$disablesStr],
    disabledHidden : false,
    defaultSelected : [$defaultStr]
  });
  
});
JS;
        $template->appendJs($js);

        return $template;
    }

}
