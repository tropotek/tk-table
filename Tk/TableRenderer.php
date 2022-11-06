<?php
namespace Tk;

use Dom\Builder;
use Dom\Renderer\Renderer;
use Dom\Template;
use Tk\Table\Action\ActionInterface;
use Tk\Table\Action\Link;
use \Tk\Table\Cell\CellInterface;
use Tk\Table\Row;
use Tk\Traits\SystemTrait;

/**
 * @author Tropotek <http://www.tropotek.com/>
 */
class TableRenderer extends Renderer
{
    use SystemTrait;

    protected Table $table;

    protected array $params = [];

    protected Builder $builder;


    public function __construct(Table $table, string $tplFile)
    {
        $this->table = $table;
        $this->init($tplFile);
    }

    protected function init(string $tplFile)
    {
        $this->builder = new Builder($tplFile);

        // get any data-opt options from the template and remove them
        $tableEl = $this->builder->getDocument()->getElementById('tpl-table');
        $cssPre = 'data-opt-';
        /** @var \DOMAttr $attr */
        foreach ($tableEl->attributes as $attr) {
            if (str_starts_with($attr->name, $cssPre)) {
                $name = str_replace($cssPre, '', $attr->name);
                $this->params[$name] = $attr->value;
            }
        }
        // Remove option attributes
        foreach ($this->params as $k => $v) {
            $tableEl->removeAttribute($cssPre . $k);
        }

        $this->setTemplate($this->buildTemplate('table'));
    }

    public function buildTemplate(string $type): ?Template
    {
        return $this->builder->getTemplate('tpl-' . $type);
    }

    public function getParam(string $name, mixed $default = null): mixed
    {
        return $this->params[$name] ?? $default;
    }

    public function getTable(): Table
    {
        return $this->table;
    }


    function show(): ?Template
    {
        // This is the cell repeat
        $template = $this->getTemplate();

        /* @var ActionInterface $action */
        foreach ($this->getTable()->getActions() as $action) {
            if (!$action->isVisible()) continue;
            $action->setTemplate($this->buildTemplate('action-button'));
            if ($action instanceof Link) {
                $action->setTemplate($this->buildTemplate('action-link'));
            }
            $tpl = $action->show();
            $template->appendTemplate('actions', $tpl);
            $template->setVisible('actions');
        }

        // Render table header elements
        $headerLabels = [];
        /** @var CellInterface $cell */
        foreach ($this->getTable()->getCells() as $cell) {
            $headerLabels[$cell->getName()] = $cell->getLabel();
        }
        $headerRow = Row::createRow($this->getTable()->getRow(), $headerLabels, 0);
        $headerRow->setTemplate($template);
        $headerRow->show();

        // Render table rows
        foreach ($this->getTable()->getList() as $rowId => $rowData) {
            // TODO: $rowId needs to add offset when using pager
            $rowRepeat = $template->getRepeat('tr');
            $headerRow = Row::createRow($this->getTable()->getRow(), $rowData, $rowId+1);
            $headerRow->setTemplate($rowRepeat);
            $headerRow->show();
            $rowRepeat->appendRepeat();
        }

        // TODO: Add on Show CallbackCollection ???

        $template->setAttr('table', $this->getTable()->getAttrList());
        $template->addCss('table', $this->getTable()->getCssList());

        return $template;
    }
}