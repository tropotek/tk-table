<?php
namespace Tk\Table\Cell;


/**
 * Class Text
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Checkbox extends Iface
{

    /**
     * Create
     *
     * @param string $property
     */
    public function __construct($property)
    {
        parent::__construct('cb_'.$property, ucfirst(preg_replace('/[A-Z]/', ' $0', $property)));

    }

    /**
     * @return string
     */
    public function getCellHeader()
    {
        $xhtml = sprintf('<span><input type="checkbox" name="%s_all" title="Select All" class="tk-tcb-head" /></span>', $this->getProperty());
        $template = \Dom\Loader::load($xhtml);

        $js = <<<JS
jQuery(function($) {

  function checkAll(headCheckbox) {
    var _cb = $(headCheckbox);
    var id = _cb.attr('name').match(/cb_([a-zA-Z0-9]+)_all/i)[1];
    var _list = _cb.parents('div.tk-table').find('input[name^=\''+id+'\']');
	if (_cb.prop('checked'))  {
	  _list.prop('checked', true);
	} else {
	  _list.prop('checked', false);
	}  
  }

  checkAll($('.tk-table .tk-tcb-head'));
  $('.tk-table .tk-tcb-head').on('change', function(e){
      checkAll(this);
  });
});
JS;
        $template->appendJs($js);

        return $template;
    }



    /**
     * Get the property value from the object
     * This should be the clean property data with no HTML or rendering attached,
     * unless the rendering code is part of the value as it will be called for
     * outputting to other files like XML or CSV.
     *
     *
     * @param object $obj
     * @param string $property
     * @return mixed
     */
    public function getPropertyValue($obj, $property)
    {
        $property = substr($property, 3);
        return parent::getPropertyValue($obj, $property);
    }


    /**
     * @param mixed $obj
     * @return string
     */
    public function getCellHtml($obj)
    {
        $prop = substr($this->getProperty(), 3);
        $propValue = $this->getPropertyValue($obj, $this->getProperty());
        $str = sprintf('<input type="checkbox" name="%s[]" value="%s" class="tk-tcb" />', $prop, htmlentities($propValue));
        return $str;
    }
}