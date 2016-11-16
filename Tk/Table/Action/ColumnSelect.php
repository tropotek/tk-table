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
     * @var \Tk\Db\Data
     */
    protected $storage = null;

    /**
     * @var array
     */
    protected $defaultHidden = array();

    /**
     * @var array
     */
    private $hidden = null;



    /**
     * Create
     *
     * @param array $defaultHidden
     * @param string $name
     * @param string $icon
     */
    public function __construct(\Tk\Db\Data $storage, $defaultHidden = array(), $name = 'columns', $icon = 'glyphicon glyphicon-list-alt')
    {
        parent::__construct($name, $icon);
        $this->storage = $storage;
        $this->defaultHidden = $defaultHidden;
    }

    /**
     * Create
     *
     * @param \Tk\Db\Data $storage
     * @param array $defaultHidden
     * @param string $name
     * @param string $icon
     * @return ColumnSelect
     */
    static function create($storage, $defaultHidden = array(), $name = 'columns', $icon = 'glyphicon glyphicon-list-alt')
    {
        return new static($storage, $defaultHidden, $name, $icon);
    }


    public function makeInstanceKey()
    {
        if (!$this->getTable()) throw new \Tk\Exception('Cannot make ID without table');
        return $this->getTable()->makeInstanceKey($this->getName());
    }


    public function getHidden()
    {
        if (!$this->hidden) {
            $this->load();
        }
        return $this->hidden;
    }

    protected function load()
    {
        $this->hidden = $this->storage->get($this->makeInstanceKey());
        if (!$this->hidden) {
            $this->hidden = $this->defaultHidden;
        }
        if (is_string($this->hidden)) {
            $this->hidden = json_decode($this->hidden);
        }
    }

    protected function save()
    {
        $this->storage->set($this->makeInstanceKey(), json_encode($this->getHidden()));
        $this->storage->save();
    }

    /**
     * @return mixed
     */
    public function execute()
    {   
        $request = $this->getTable()->getRequest();

        vd($this->getHidden());

        // save when onChange event fired

        $this->save();


    }

    /**
     * @return string|\Dom\Template
     */
    public function getHtml()
    {
        $template = parent::getHtml();
        $btnId = $this->getTable()->makeInstanceKey($this->getName());

        $js = <<<JS
jQuery(function ($) {

  // Allow Bootstrap dropdown menus to have forms/checkboxes inside, 
  // and when clicking on a dropdown item, the menu doesn't disappear.
  $(document).on('click', '.dropdown-menu.checkbox-menu', function(e) {
    e.stopPropagation();
  });
  
  // Fire an action on checkbox change
  $('#$btnId').parent().find('[type=checkbox]').on('change', function (e) {
    
    //console.log('----------------------' + $(this).prop('checked'));
    // show/hide selected column
    
    
    // Find all hiden column names.
    
    url = '';
    // Send new hidden column list to server for saving via ajax.
    $.post(url, {hidden: []}, function (result) {
      // .. not sure we do anything here    
    });
  });
  

});
JS;
        $template->appendJs($js);

        return $template;
    }


    /**
     *
     * @return \Dom\Template
     */
    public function getTemplate()
    {
        $xhtml = <<<XHTML
<div class="btn-group">
  
  <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" var="btn">
    <i var="icon" choice="icon"></i>
    <span var="btnTitle"></span>
    <span class="caret"></span>
  </button>
  <ul class="dropdown-menu checkbox-menu">
    <li><label for="option1" class="small"><input type="checkbox" id="option1" name="option[]" value="fieldName"/> Field Name</label></li>
    <li><label for="option2" class="small"><input type="checkbox" id="option2" name="option[]" value="fieldName"/> Field Name</label></li>
    <li><label for="option3" class="small"><input type="checkbox" id="option3" name="option[]" value="fieldName"/> Field Name</label></li>
    <li><label for="option4" class="small"><input type="checkbox" id="option4" name="option[]" value="fieldName"/> Field Name</label></li>
  </ul>
</div>
XHTML;
        return \Dom\Loader::load($xhtml);
    }

}
