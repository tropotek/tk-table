<?php
namespace Tk\Table\Action;

use Tk\Collection;
use Tk\Table\Cell;
use Tk\Table\Cell\OrderBy;
use Tk\Table\Cell\RowSelect;
use Tk\Ui\Traits\AttributesTrait;
use Tk\Uri;
use Tk\Table\Action;

/**
 * Allow users to show/hide table columns
 * Example:
 * ```
 *     $this->appendCell('created')
 *         ->setAttr(ColumnSelect::ATTR_HIDE, true);    // hide the cell by default
 *     ...
 *     $this->table->appendAction(ColumnSelect::create());
 *
 * ```
 */
class ColumnSelect extends Action
{
    use AttributesTrait;

    const string SID = 'columnSelect';

    // ignore cell in the select list
    const string ATTR_IGNORE = 'data-column-ignore';
    // hide cell in the default visible list
    const string ATTR_HIDE = 'data-column-hide';

    protected string $icon = '';

    /** @var list<string> */
    protected array $selected = [];

    protected Collection $session;


    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->addCss('btn btn-sm btn-light');
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

        // get selected
        $this->session = $this->getTable()->getTableSession();

        // create the default visible list
        $defaultSelected = [];
        foreach ($this->getTable()->getCells() as $cell) {
            if ($this->isVisible($cell)) {
                $defaultSelected[] = $cell->getName();
            }
        }
        // get selected
        $this->selected = $this->session->get(self::SID, $defaultSelected);
        // set selected
        $this->session->set(self::SID, $this->selected);

        // get the submitted column list
        $action = trim($_POST['action'] ?? '');

        // Save selected columns
        if ($action == $this->getTable()->makeRequestKey($this->getName())) {
            $this->selected = $_POST[$this->getName()];
            // set selected
            $this->session->set(self::SID, $this->selected);
            Uri::create()->redirect();
        }

        // Reset columns to selected defaults
        if ($action == $this->getTable()->makeRequestKey($this->getName().'_reset')) {
            // reset selected
            $this->session->remove(self::SID);
            Uri::create()->redirect();
        }
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
            if (in_array($cell->getName(), $this->selected)) {
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
        $actionReset = $this->getTable()->makeRequestKey($this->getName().'_reset');
        return <<<HTML
<div class="btn-group dropstart float-end tk-column-select" role="group">
    <button class="{$this->getCssString()} dropdown-toggle" {$this->getAttrString()} data-bs-auto-close="outside" data-bs-toggle="dropdown" aria-expanded="false">
      <i class="{$this->icon}"></i> {$this->getLabel()}
      <i class="mdi mdi-chevron-down"></i>
    </button>
    <ul class="dropdown-menu">
        <li class="dropdown-item border-bottom">
            <button type="submit" name="action" value="{$action}" class="btn btn-sm btn-light btn-outline-field m-0 btn-save" disabled>Save Selected</button>
            <button type="submit" name="action" value="{$actionReset}" class="btn btn-sm btn-light btn-outline-field m-0 float-end btn-reset" title="Reset Defaults"><i class="fas fa-sync"></i></button>
        </li>
        {$buttonHtml}
    </ul>
</div>
HTML;
    }

    protected function isIgnored(Cell $cell): bool
    {
        if ($cell instanceof RowSelect || $cell instanceof OrderBy) return true;
        if (truefalse($cell->getAttr(self::ATTR_IGNORE, '')) ?? false) return true;
        if (in_array($cell->getName(), ['actions', 'edit'])) return true;
        return false;
    }

    protected function isVisible(Cell $cell): bool
    {
        if ($this->isIgnored($cell)) return false;
        if (truefalse($cell->getAttr(self::ATTR_HIDE, '')) ?? false) return false;
        return true;
    }


}