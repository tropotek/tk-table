<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Table\Ui;

/**
 * A Limit component allows the user to change the number of records per page.
 *
 *
 * @package Table\Ui
 */
class Limit extends \Mod\Renderer
{

    /**
     * @var int
     */
    private $limit = 0;

    /**
     * Create the object instance
     *
     * @param int $limit
     */
    public function __construct($limit = 0)
    {
        $this->limit = $limit;
    }

    /**
     * Make a pager from a db tool object
     *
     * @param \Tk\Db\Tool $tool
     * @return \Table\Ui\Limit
     */
    static function createFromTool(\Tk\Db\Tool $tool)
    {
        $obj = new self($tool->getLimit());
        $obj->setInstanceId($tool->getInstanceId());
        return $obj;
    }

    /**
     * Render the widget.
     *
     */
    public function show()
    {
        $template = $this->getTemplate();
        $domform = $template->getForm();
        $select = $domform->getFormElement('limit');

        $select->setValue($this->limit);
        $select->setAttribute('name', $this->getObjectKey(\Tk\Db\Tool::REQ_LIMIT));
        
        $template->appendJsUrl(\Tk\Url::create('/assets/tk-jslib/Url.js'));
        $js = <<<JS
jQuery(function($) {

   $('.tk-limit select').change(function(e) {  
      if ($(this).val() == 0) {
          if (!confirm('WARNING: If there are many records this action could be slow.')) {
            return false;
          }
      }
      this.form.submit();
   });

});
JS;
        $template->appendJs($js);
        
    }

    /**
     * makeTemplate
     *
     * @return Dom_Template
     */
    public function __makeTemplate()
    {
        $xmlStr = '<?xml version="1.0"?>
<div class="tk-limit" var="limitUl">
    Show:
    <select class="no2 form-control input-sm" name="limit" var="select">
      <option value="0">-- ALL --</option>
      <option value="10">10</option>
      <option value="25">25</option>
      <option value="50">50</option>
      <option value="100">100</option>
    </select>
  
</div>';
        return \Mod\Dom\Loader::load($xmlStr, $this->getClassName());
    }



}