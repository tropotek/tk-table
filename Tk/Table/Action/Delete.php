<?php
namespace Tk\Table\Action;


use Dom\Template;
use Tk\CallbackCollection;

/**
 * @author Tropotek <http://www.tropotek.com/>
 */
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

    public function execute()
    {
        parent::execute();

//        $reqName = $this->getTable()->makeInstanceKey($this->getName());
//        $request = $this->getTable()->getRequest();
//        if (empty($request->get($reqName))) {
//            return;
//        }
//        $selected = $request->get($this->getCheckboxName());
//        if (!is_array($selected)) return;
//
//        /* @var \Tk\Db\Map\Model $obj */
//        foreach($this->getTable()->getList() as $obj) {
//            if (!is_object($obj)) continue;
//            $keyValue = 0;
//            if (property_exists($obj, $this->getCheckboxName())) {
//                $keyValue = $obj->{$this->getCheckboxName()};
//            }
//            if (in_array($keyValue, $selected) && !in_array($keyValue, $this->getExcludeIdList())) {
//                $propagate = true;
//                $r = $this->getOnDelete()->execute($this, $obj);
//                if ($r !== null && is_bool($r)) $propagate = $r;
//                if ($propagate) {
//                    $obj->delete();
//                }
//            }
//        }

//        \Tk\Uri::create()->remove($this->getTable()->makeInstanceKey($this->getName()))->redirect();
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
    function updateBtn(btn) {
      var cbName = btn.data('cb-name');
      if(btn.closest('.tk-table').find('.table-body input[name^="'+cbName+'"]:checked').length) {
        btn.removeAttr('disabled');
      } else {
        btn.attr('disabled', 'disabled');
      }
    }

    $('.tk-action-delete').each(function () {
      var btn = $(this);
      var cbName = btn.data('cb-name');
      btn.on('click', function () {
        var selected = $(this).closest('.tk-table').find('.table-body input[name^="'+cbName+'"]:checked');
        return selected.length > 0;
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
