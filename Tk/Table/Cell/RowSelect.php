<?php
namespace Tk\Table\Cell;

use Dom\Template;
use Symfony\Component\HttpFoundation\Request;
use Tk\Uri;

class RowSelect extends CellInterface
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

    public function getCellValue(): string
    {
        $value = $this->getValue();
        if (is_null($value)) return '';

        $checked = '';
        if ($this->useValue && ($value == $this->getName() || strtolower($value) === 'true' || strtolower($value) === 'yes' || $value == 1)) {
            $checked = ' checked="checked"';
        }

        return sprintf('<input type="checkbox" name="%s[]" value="%s" class="tk-tcb" title="%s: %s" %s/>',
            $this->getName(),
            htmlentities($value),
            $this->getName(),
            htmlentities($value),
            $checked);
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
        return <<<JS
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
  init();
  $('form').on(EVENT_INIT_FORM, document, init).each(init);

  // TODO: See if we need to implemnt this for dynamic html updates
  //$('.tk-table .tk-table-form').each(init);
  // $('.tk-table').on('tk-table-update', '.tk-table-form', init);
  // $('.tk-table .tk-table-form').trigger('tk-table-update');

});
JS;
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