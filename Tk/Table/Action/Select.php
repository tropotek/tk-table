<?php
namespace Tk\Table\Action;

use Tk\CallbackCollection;
use Tk\Ui\Traits\AttributesTrait;
use Tk\Uri;
use Tk\Table\Action;
use Tk\Table\Cell\RowSelect;

/**
 * This action depends on \Tk\Table\Cell\RowSelect Cell
 * Use this to attach an action that can be triggered on selected rows
 *
 * NOTE: This Action does not call the onExecute() or onShow() callback queues
 */
class Select extends Action
{
    use AttributesTrait;

    protected string             $confirmStr = 'Execute the selected records?';
    protected string             $icon       = '';
    protected array              $actions    = [];
    protected RowSelect          $rowSelect;
    protected CallbackCollection $onSelect;


    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->onSelect = CallbackCollection::create();
        $this->addCss('btn btn-sm btn-light tk-action-select');
        $this->setAttr('disabled');
        $this->setAttr('data-confirm', $this->confirmStr);
    }

    public static function create(RowSelect $rowSelect, string $name = 'select', string $icon = 'fa fa-fw fa-check'): self
    {
        $obj = new self($name);
        $obj->rowSelect = $rowSelect;
        $obj->icon = $icon;
        $obj->setAttr('data-row-select', $rowSelect->getName());
        return $obj;
    }

    public function execute(): void
    {
        $selectName = $this->getTable()->makeRequestKey($this->getName());
        $this->setActive(isset($_POST[$selectName]));
        if (!$this->isActive()) return;

        $selected = $_POST[$this->rowSelect->getName()] ?? [];
        $value = trim($_POST[$selectName]);
        $this->getOnSelect()->execute($this, $selected, $value);
        Uri::create()->redirect();
    }

    /**
     * @note see `tkTable.js` for supporting JS to this action
     */
    public function getHtml(): string
    {
        $selectName = $this->getTable()->makeRequestKey($this->getName());
        if (empty($this->actions)) {
            return <<<HTML
<button type="submit" name="{$selectName}" value="{$selectName}" class="{$this->getCssString()}" {$this->getAttrString()}>
    <i class="{$this->icon}"></i> {$this->getLabel()}
</button>
HTML;
        }

        $attr = '';
        if ($this->getAttr('data-confirm')) {
            $attr = sprintf('data-confirm="%s"', $this->getAttr('data-confirm'));
            $this->removeAttr('data-confirm');
        }
        $buttonHtml = '';
        foreach ($this->actions as $name => $val) {
            $buttonHtml .= sprintf('<li><button class="dropdown-item" type="submit" name="%s" value="%s" %s>%s</button></li>',
                $selectName, $val, $attr, $name);
        }

        return <<<HTML
<div class="btn-group" role="group">
    <button type="button" class="{$this->getCssString()} dropdown-toggle"  {$this->getAttrString()} data-bs-toggle="dropdown" aria-expanded="false">
      {$this->getLabel()}
      <i class="mdi mdi-chevron-down"></i>
    </button>
    <ul class="dropdown-menu">
        {$buttonHtml}
    </ul>
</div>
HTML;
    }

    protected function getConfirmStr(): string
    {
        return $this->confirmStr;
    }

    public function setConfirmStr(string $confirmStr): static
    {
        $this->confirmStr = $confirmStr;
        $this->setAttr('data-confirm', $this->confirmStr);
        return $this;
    }

    /**
     * @callable function (\Tk\Table\Action\Select $action, array $selected, string $value): ?bool { }
     */
    public function addOnSelect(callable $callable, int $priority = CallbackCollection::DEFAULT_PRIORITY): static
    {
        $this->getOnSelect()->append($callable, $priority);
        return $this;
    }

    public function getOnSelect(): CallbackCollection
    {
        return $this->onSelect;
    }

    public function setActions(array $actions): static
    {
        $this->actions = $actions;
        return $this;
    }

}