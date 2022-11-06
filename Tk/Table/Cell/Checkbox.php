<?php
namespace Tk\Table\Cell;


use Dom\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Tropotek <http://www.tropotek.com/>
 */
class Checkbox extends CellInterface
{
    /**
     * If true the checkbox is set to checked if the property value evaluates to true
     */
    protected bool $useValue = false;


    public function __construct(string $property)
    {
        parent::__construct($property);
        $this->setLabel('');
        $this->addCss('tk-tcb-cell');
    }

    public function execute(Request $request): void
    {
        // TODO: see if we can detect a form submission and then add the selected values to an array
        //       Then implement getSelected() and isSelected($cellName) or similar

        if ($request->request->has($this->getName())) {
            vd($request->get('id'));
            //vd('Selected checkbox', $request->request->get($this->getName(), ''));

        }

    }

    function show(): ?Template
    {
        $template = $this->getTemplate();

        $this->decorate($template);

        $prop = $this->getName();
        $propValue = $this->getValue();

        $checked = '';
        if ($this->useValue && ($propValue == $prop || $propValue == 1 || strtolower($propValue) === 'true' || strtolower($propValue) === 'yes'))
            $checked = ' checked="checked"';
        $html = sprintf('<input type="checkbox" name="%s[]" value="%s" class="tk-tcb" title="%s: %s" %s/>', $prop, htmlentities($propValue), $prop, htmlentities($propValue), $checked);

        $template->insertHtml('td', $html);

        return $template;
    }

    public function showHeader(): ?Template
    {
        if ($this->getLabel()) return parent::showHeader();

        // This is the cell repeat
        $template = $this->getTemplate();
        if (!$this->getRow()->isHead()) return $template;

        $template->appendJs($this->getJs());

        $html = sprintf('<span><input type="checkbox" name="%s_all" title="Select All" class="tk-tcb-head" /></span>', $this->getName());

        $template->insertHtml('td', $html);
        return $template;
    }

    protected function getJs(): string
    {
        $js = <<<JS
jQuery(function($) {

  var init = function () {
    var form = $(this);
    var table = form.parent();
    if (!table.is('.tk-table')) return;

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

    var head = form.find('.tk-tcb-head');
    head.on('change', function(e){
        checkAll(this);
    }).trigger('change');

  };
  $('.tk-table .tk-table-form').each(init);

  // TODO: See if we need to implemnt this for dynamic html updates
  // $('.tk-table').on('tk-table-update', '.tk-table-form', init);
  // $('.tk-table .tk-table-form').trigger('tk-table-update');

});
JS;
        return $js;
    }

    public function isUseValue(): bool
    {
        return $this->useValue;
    }

    public function setUseValue(bool $useValue): static
    {
        $this->useValue = $useValue;
        return $this;
    }

}