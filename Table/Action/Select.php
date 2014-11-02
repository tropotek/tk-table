<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Table\Action;

/**
 * For this action to work the object must contain a delete() method
 *
 *
 * @package \Table\Action
 */
class Select extends Iface
{
    /**
     * @var \Mod\Module
     */
    protected $module = null;

    /**
     * @var array
     */
    protected $selectList = array();


    /**
     * Create a select action
     *
     * @param \Mod\Module $module
     * @param array $selectList
     * @return \Table\Action\Select
     */
    static function create($module, $selectList)
    {
        $obj = new self('action', \Tk\Request::getInstance()->getRequestUri(), 'fa fa-wrench');
        $obj->setLabel('Select Action');
        $obj->module = $module;
        $obj->selectList = $selectList;
        return $obj;
    }



    /**
     * (non-PHPdoc)
     * @see \Table\Action::execute()
     */
    public function execute($list)
    {

        $selected = $this->getRequest()->get($this->getObjectKey(\Table\Cell\Checkbox::CB_NAME));
        $event = $this->getRequest()->get('select');

        if (method_exists($this->module, $event)) {
            $this->module->$event($selected, $list);
        }
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
        $eid = $this->getObjectKey($this->event);
        $js = <<<JS
jQuery(function($) {
    $('.selectAction select').change(function () {
        if (confirm('Are you sure you want to perform this action on the records?')) {
            uOn();
            tkFormSubmit(this.form, '$eid');
        }
        $('.selectAction select').val('');
    });
    $('.selectAction .btn').hide();
});
JS;
        $template->appendJs($js);

        $domForm = $template->getForm();
        $el = $domForm->getFormElement('select');

        foreach ($this->selectList as $k => $v) {
            $el->appendOption($k, $v);
        }


        return $template;
    }



    /**
     * makeTemplate
     *
     * @return string
     */
    public function __makeTemplate()
    {
        $xmlStr = <<<XML
<?xml version="1.0"?>
<div class="selectAction pull-left">
    <label var="label">Group Actions: </label>
    <select id="fid-name" name="select" var="select" class="form-control input-sm" style="width: 150px;" ></select>
    <input type="submit" name="go" value="Go" class="btn btn-default btn-xs" var="btn" />
</div>
XML;

        $template = \Mod\Dom\Loader::load($xmlStr, $this->getClassName());
        return $template;
    }
}
