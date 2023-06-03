<?php
namespace Tk\Table\Cell;

use Dom\Template;
use Symfony\Component\HttpFoundation\Request;
use Tk\Uri;

class Checkbox extends CellInterface
{
    /**
     * If true the checkbox is set to checked if the property value evaluates to true
     */
    protected bool $useValue = false;

    /**
     * selected values are only available on the action submit event
     */
    protected array $selected = [];


    public function __construct(string $property)
    {
        parent::__construct($property);
        $this->setLabel('');
        $this->addCss('tk-tcb-cell text-center');
    }

    public function execute(Request $request): void
    {
        if ($request->request->has($this->getName())) {
            $this->selected = $request->get($this->getName());
        }
    }

    function show(): ?Template
    {
        $template = $this->getTemplate();

        $this->setValue($this->getOnValue()->execute($this, $this->getValue()) ?? $this->getValue());

        $prop = $this->getName();
        $propValue = $this->getValue();

        $checked = '';
        if ($this->useValue && ($propValue == $prop || $propValue == 1 || strtolower($propValue) === 'true' || strtolower($propValue) === 'yes'))
            $checked = ' checked="checked"';
        $html = sprintf('<input type="checkbox" name="%s[]" value="%s" class="tk-tcb" title="%s: %s" %s/>', $prop, htmlentities($propValue), $prop, htmlentities($propValue), $checked);

        $html == $this->getOnShow()->execute($this, $html) ?? $html;
        $template->insertHtml('td', $html);

        $this->decorate($template);

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

  let init = function () {
    let form = $(this);
    $('.tk-tcb-head', form).on('change', function(e) {
      let cb = $(this);
      let name = cb.attr('name').match(/([a-zA-Z0-9]+)_all/i)[1];
      let list = $('.table-body input[name^=\''+name+'\']', form);
      list.prop('checked', cb.prop('checked')).trigger('change');
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

    public function getSelected(): array
    {
        return $this->selected;
    }

    public function isSelected(string $value): bool
    {
        return in_array($value, $this->getSelected());
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

    /**
     * Disable the URL for this cell
     */
    public function setUrl(null|string|Uri $url): static
    {
        return $this;
    }

}