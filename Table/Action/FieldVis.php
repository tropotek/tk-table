<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Table\Action;

/**
 * 
 *
 * @TODO: Make this through a jquery plugin hide/show columns.....
 */
class FieldVis extends Iface
{

    

    /**
     * Create a delete action
     *
     * @return \Table\Action\Csv
     */
    static function create()
    {
        $obj = new self('csv', \Tk\Request::getInstance()->getRequestUri());
        return $obj;
    }


    /**
     * (non-PHPdoc)
     * @param \Tk\Db\ArrayObject $list
     * @see \Table\Action\Iface::execute()
     */
    public function execute($list)
    {
        
    }

    /**
     * Get the action HTML to insert into the Table.
     * If you require to use form data be sure to submit the form using javascript not just a url anchor.
     * Use submitForm() found in Js/Util.js to submit a form with an event
     *
     * @param array $list
     * @return \Dom\Template You can also return HTML string
     */
    public function getHtml($list)
    {
        $template = $this->__makeTemplate();
        
        
        $js = <<<js
jQuery(function($) {
  $.fieldViz.init('#{$this->getTable()->getId()}');
});
js;
        $template->appendJs($js);
        
        return $template;
//        $js = sprintf("$(this).unbind('click'); return confirm('%s');", $this->confirmMsg);
//        $url = $this->getUri()->set($this->getObjectKey('csv'));
//        return sprintf('<a class="btn btn-default btn-xs" href="%s" onclick="%s" title="%s"><span class="%s"></span> %s</a>',
//            $url->toString(), $js, $this->notes, $this->getClassString(), $this->label);
    }


    /**
     * makeTemplate
     *
     * @return string
     */
    public function __makeTemplate()
    {
        $xmlStr = <<<HTML
<?xml version="1.0" encoding="UTF-8"?>
<div class="btn-group pull-right FieldVis">
  <button class="btn btn-default btn-xs dropdown-toggle" type="button" data-toggle="dropdown">
    Fields
    <span class="caret" var="caret"></span>
  </button>
  <ul class="dropdown-menu" role="menu">
    <li></li>
  </ul>
</div>
HTML;
        $template = \Mod\Dom\Loader::load($xmlStr, $this->getClassName());
        return $template;
    }

}