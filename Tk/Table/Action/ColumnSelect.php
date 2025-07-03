<?php
namespace Tk\Table\Action;

use Tk\CallbackCollection;
use Tk\Collection;
use Tk\System;
use Tk\Table\Cell;
use Tk\Table\Cell\OrderBy;
use Tk\Table\Cell\RowSelect;
use Tk\Ui\Traits\AttributesTrait;
use Tk\Uri;
use Tk\Table\Action;

/**
 * Allow users to show/hide table columns
 *
 * @note When using ColumnSelect you must ensure the jQuery plugin `columnSelect` is installed
 * @depends /tk-table/templates/tkTable.js
 */
class ColumnSelect extends Action
{
    use AttributesTrait;

    const string SID = 'columnSelect';

    // do not include column in selectable list
    const string ATTR_IGNORE = 'data-column-ignore';
    // Hide column on default view
    const string ATTR_HIDE = 'data-column-hide';

    protected string $icon = '';

    /** @var list<string> */
    protected array $visible = [];

    protected Collection $session;


    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->addCss('btn btn-sm btn-light tk-column-select');
    }

    public static function create(string $name = 'columns', string $icon = 'fas fa-eye'): self
    {
        $obj = new self($name);
        $obj->icon = $icon;
        $obj->label = '';
        return $obj;
    }

    public function execute(): void
    {
        parent::execute();
        if (!$this->isActive()) return;

        $actionId = $this->getTable()->makeRequestKey($this->getName());

        // get lists
        $this->session = $this->getTable()->getTableSession();
        if (System::isRefreshCacheRequest()) {
            $this->session->remove(self::SID);
        }

        // create default visible list
        $defaultVisible = [];
        foreach ($this->getTable()->getCells() as $cell) {
            if ($this->isVisible($cell)) {
                $defaultVisible[] = $cell->getName();
            }
        }
        $this->visible = $this->session->get(self::SID, $defaultVisible);
        $this->session->set(self::SID, $this->visible);

        // get submitted column list
        $action = trim($_POST['action'] ?? '');
        if ($action !== $actionId) return;
        $this->visible = $_POST[$this->getName()];
        $this->session->set(self::SID, $this->visible);
        Uri::create()->redirect();
    }

    /**
     * @note see `tkTable.js` for supporting JS to this action
     */
    public function getHtml(): string
    {
        $buttonHtml = '';
        foreach ($this->getTable()->getCells() as $cell) {
            if ($this->isIgnored($cell)) continue;

            $checked = '';
            if (in_array($cell->getName(), $this->visible)) {
                $checked = 'checked';
            } else {
                $cell->setAttr('style', 'display:none;');
                $cell->setHeaderAttr('style', 'display:none;');
            }
            $cb = sprintf('<input type="checkbox" name="%s" value="%s" id="%s" %s>', $this->getName().'[]', $cell->getName(), 'oid-'.$cell->getName(), $checked);
            $buttonHtml .= sprintf('<li><label class="dropdown-item" type="submit" name="%s" value="%s" for="%s">%s %s</label></li>',
                $cell->getName(), $cell->getName(), 'oid-'.$cell->getName(), $cb, $cell->getHeader());
        }

        $action = $this->getTable()->makeRequestKey($this->getName());
        return <<<HTML
<div class="btn-group float-end" role="group">
    <button class="{$this->getCssString()} dropdown-toggle" {$this->getAttrString()} data-bs-auto-close="outside" data-bs-toggle="dropdown" aria-expanded="false">
      <i class="{$this->icon}"></i> {$this->getLabel()}
      <i class="mdi mdi-chevron-down"></i>
    </button>
    <ul class="dropdown-menu">
        {$buttonHtml}
        <li class="dropdown-item"><button type="submit" name="action" value="{$action}" class="btn btn-sm btn-light btn-outline-field width-lg">Show</button></li>
    </ul>
</div>
HTML;
    }

    protected function isIgnored(Cell $cell): bool
    {
        if ($cell instanceof RowSelect || $cell instanceof OrderBy) return true;
        if (truefalse($cell->getAttr(self::ATTR_IGNORE, false))) return true;
        if (in_array($cell->getName(), ['actions'])) return true;
        return false;
    }

    protected function isVisible(Cell $cell): bool
    {
        if ($this->isIgnored($cell)) return false;
        if (truefalse($cell->getAttr(self::ATTR_HIDE, false))) return false;
        return true;
    }


}