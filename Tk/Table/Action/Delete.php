<?php
namespace Tk\Table\Action;

use Dom\Template;
use Symfony\Component\HttpFoundation\Request;
use Tk\CallbackCollection;
use Tk\Db\Mapper\Model;
use Tk\ObjectUtil;
use Tk\Table;
use Tk\Uri;

class Delete extends Button
{

    protected string $checkboxName = 'id';

    protected array $excludeIdList = [];

    protected CallbackCollection $onDelete;

    protected string $confirmStr = 'Are you sure you want to delete the selected records?';


    public function __construct(string $name = 'delete', string $checkboxName = 'id', $icon = 'fa fa-times')
    {
        $this->onDelete = CallbackCollection::create();
        parent::__construct($name, $icon);
        $this->addCss('tk-action-delete');
        $this->setCheckboxName($checkboxName);
    }

    public function execute(Request $request)
    {
        parent::execute($request);

        if (!$this->isTriggered()) return;

        /** @var Table\Cell\Checkbox $checkbox */
        $checkbox = $this->getTable()->getCell($this->getCheckboxName());

        /* @var object|array $obj */
        foreach($this->getTable()->getList() as $obj) {
            if (is_array($obj)) {
                $keyValue = $obj[$this->getCheckboxName()] ?? '';
            } else {
                $keyValue = ObjectUtil::getPropertyValue($obj, $this->getCheckboxName());
            }

            if (in_array($keyValue, $this->getExcludeIdList())) continue;

            if ($keyValue && $checkbox->isSelected($keyValue)) {
                $propagate = true;
                $r = $this->getOnDelete()->execute($this, $obj);
                if (is_bool($r)) $propagate = $r;
                if ($propagate && $obj instanceof Model) {
                    $obj->delete();
                }
            }
        }

        Uri::create()->remove($this->getTable()->makeInstanceKey($this->getName()))->redirect();
    }

    public function show(): ?Template
    {
        if (!$this->hasAttr('title'))
            $this->setAttr('title', 'Delete Selected Records');
        if (!$this->hasAttr('data-confirm'))
            $this->setAttr('data-confirm', $this->getConfirmStr());

        $this->setAttr('disabled');
        $this->setAttr('data-cb-name', $this->getCheckboxName());

        $template = parent::show();

        $template->appendJs($this->getJs());
        return $template;
    }

    protected function getJs(): string
    {
        $js = <<<JS
jQuery(function($) {

  var init = function () {
    let form = $(this);

    function updateBtn(btn) {
      var cbName = btn.data('cb-name');
      btn.removeAttr('disabled');
      if(!$('.table-body input[name^="'+cbName+'"]:checked', form).length) {
        btn.attr('disabled', 'disabled');
      }
    }

    $('.tk-action-delete', form).each(function () {
      var btn = $(this);
      var cbName = btn.data('cb-name');
      btn.on('click', function () {
        return $('.table-body input[name^="'+cbName+'"]:checked', form).length > 0;
      });
      btn.closest('.tk-table').on('change', '.table-body input[name^="'+cbName+'"]', function () { updateBtn(btn); });
      updateBtn(btn);
    });
  }
  $('.tk-table .tk-table-form').each(init);


  // TODO: See if we need to implemnt this for dynamic html updates
  // $('.tk-table').on('tk-table-update', '.tk-table-form', init);
  // $('.tk-table .tk-table-form').trigger('tk-table-update');

});
JS;
        return $js;
    }


    public function setTable(Table $table): static
    {
        parent::setTable($table);
        $checkbox = $this->getTable()->getCell($this->getCheckboxName());
        if (!$checkbox instanceof Table\Cell\Checkbox) {
            throw new Table\Exception("Checkbox cell {$this->getCheckboxName()} not found in table.");
        }
        return $this;
    }

    public function getCheckboxName(): string
    {
        return $this->checkboxName;
    }

    public function setCheckboxName(string $checkboxName): static
    {
        $this->checkboxName = $checkboxName;
        return $this;
    }

    public function setExcludeIdList(array $array): static
    {
        $this->excludeIdList = $array;
        return $this;
    }

    public function getExcludeIdList(): array
    {
        return $this->excludeIdList;
    }

    /**
     * EG:  function (\Tk\Table\Action\Delete $action, $obj): ?bool { }
     * Return false from the callback to stop the call to $obj->delete()
     */
    public function addOnDelete(callable $callable, int $priority = CallbackCollection::DEFAULT_PRIORITY): static
    {
        $this->getOnDelete()->append($callable, $priority);
        return $this;
    }

    public function getOnDelete(): CallbackCollection
    {
        return $this->onDelete;
    }

    protected function getConfirmStr(): string
    {
        return $this->confirmStr;
    }

    public function setConfirmStr(string $confirmStr): static
    {
        $this->confirmStr = $confirmStr;
        return $this;
    }

}
