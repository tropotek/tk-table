<?php
namespace Tk\Table\Cell;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
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
        parent::__construct($property, ucfirst(preg_replace('/[A-Z]/', ' $0', $property)));
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
    var name = _cb.attr('name').match(/([a-zA-Z0-9]+)_all/i)[1];
    var _list = _cb.parents('div.tk-table').find('.table-body input[name^=\''+name+'\']');
	if (_cb.prop('checked'))  {
	  _list.prop('checked', true);
	} else {
	  _list.prop('checked', false);
	}
	_list.trigger('change');
  }
  
  var head = $('.tk-table .tk-tcb-head');
  checkAll(head);
  head.on('change', function(e){
      checkAll(this);
  });
});
JS;
        $template->appendJs($js);

        return $template;
    }

    /**
     * @param mixed $obj
     * @param int|null $rowIdx The current row being rendered (0-n) If null no rowIdx available.
     * @return string
     */
    public function getCellHtml($obj, $rowIdx = null)
    {
        $prop = $this->getProperty();
        $propValue = $this->getPropertyValue($obj);
        $str = sprintf('<input type="checkbox" name="%s[]" value="%s" class="tk-tcb" />', $prop, htmlentities($propValue));
        return $str;
    }

}