<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Table\Cell;

/**
 * The dynamic table Cell
 *
 *
 * @package Table\Cell
*/
class Checkbox extends Iface
{
    const CB_NAME = 'cb';


    public function __construct()
    {
        parent::__construct(self::CB_NAME, '');
    }

    /**
     * Get the table data
     *
     * @param \Tk\Object $placement
     * @return string
     */
    public function getTd($placement)
    {
        $str = '<input type="checkbox" name="' . $this->getObjectKey(self::CB_NAME) . '[]" value="' . $placement->id . '" />';
        return $str;
    }

    /**
     * Get the table header data
     *
     * @internal param \Tk\Object $obj
     * @return string
     */
    public function getTh()
    {
        $str = '<span><input type="checkbox" name="' . $this->getObjectKey(self::CB_NAME) . '" id="fid-' . $this->getObjectKey(self::CB_NAME) . '" onchange="checkAll(this);"/></span>';
        $template = \Mod\Dom\Loader::load($str, get_class($this));
        $tdclass = 'm' . ucfirst(self::CB_NAME);
        $js = <<<JS
// Enable a tr onclick event to toggle check box
jQuery(function($) {
  $('.Table td.$tdclass input:checkbox').click(function(e){
	if ($(this).attr('checked')) {
	  $(this).removeAttr('checked');
	} else {
	  $(this).attr('checked', 'checked');
	}
  });
  $('.Table td.$tdclass input:checkbox').parents('tr').click(function (e) {
    if ($(this).find('td.$tdclass input:checkbox').attr('checked')) {
      $(this).find('td.$tdclass input:checkbox').removeAttr('checked');
    } else {
      $(this).find('td.$tdclass input:checkbox').attr('checked', 'checked');
    }
  });
});

// Table_Cell_Checkbox
function checkAll(checkbox) {
	var form = checkbox.form;
	var fieldName = arguments[1] ? arguments[1] : checkbox.name;
	for (i = 0; i < form.elements.length; i++) {
		if ((form.elements[i].type == "checkbox") && (form.elements[i].name.indexOf(fieldName) > -1)) {
			if (!(form.elements[i].value == "DISABLED" || form.elements[i].disabled)) {
				form.elements[i].checked = checkbox.checked;
			}
		}
	}
	return true;
}
JS;
        $template->appendJs($js);
        return $template;
    }

}
