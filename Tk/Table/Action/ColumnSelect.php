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


    public function makeInstanceKey()
    {
        if (!$this->getTable()) throw new \Tk\Exception('Cannot make ID without table');
        return $this->getTable()->makeInstanceKey($this->getName());
    }

    protected function load()
    {
        vd('column select load');
    }

    protected function save()
    {
        vd('column select save');

    }

    /**
     * @return mixed
     */
    public function execute()
    {   
        $request = $this->getTable()->getRequest();

        // save when onChange event fired

        $this->save();

        vd('column select execute');

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


        $js = <<<JS
jQuery(function ($) {
    
  $('#$tableId').columnSelect({
    buttonId : '$btnId'
  });
  
});
JS;
        $template->appendJs($js);

        return $template;
    }

}
