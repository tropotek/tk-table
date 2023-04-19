<?php
namespace Tk;

use Dom\Builder;
use Dom\Renderer\Renderer;
use Dom\Renderer\RendererInterface;
use Dom\Template;
use Tk\Db\Mapper\Result;
use Tk\Table\Action\ActionInterface;
use Tk\Table\Action\Link;
use \Tk\Table\Cell\CellInterface;
use Tk\Table\Row;
use Tk\Table\Ui\Limit;
use Tk\Table\Ui\Pager;
use Tk\Table\Ui\Results;
use Tk\Table\Ui\UiInterface;
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

    protected Collection $footer;

    /**
     * Enable Rendering of the footer
     */
    private bool $footerEnabled = true;


    public function __construct(Table $table, string $tplFile = null)
    {
        $this->footer = new Collection();
        $this->table = $table;
        if (!is_file($tplFile ?? '')) {
            $tplFile = $this->makePath($this->getConfig()->get('path.template.table'));
        }
        $this->init($tplFile);

        $this->appendFooter('results', Results::create());
        $this->appendFooter('pager',  Pager::create());
        $this->appendFooter('limit',  Limit::create());
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

        // load any cell templates
        foreach ($this->getTable()->getCells() as $cell) {
            $tpl = $this->buildTemplate('tpl-cell-'.lcfirst(ObjectUtil::basename($cell)));
            if ($tpl) {
                Log::warning('Loading table cell template: ' . 'tpl-cell-'.lcfirst(ObjectUtil::basename($cell)));
                $cell->setTemplate($tpl);
            }
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

    public function getFooterList(): Collection
    {
        return $this->footer;
    }

    public function appendFooter(string $name, UiInterface $renderer): static
    {
        $renderer->setTable($this->getTable());
        $this->getFooterList()->append($name, $renderer);
        return $this;
    }

    public function isFooterEnabled(): bool
    {
        return $this->footerEnabled;
    }

    public function setFooterEnabled(bool $b = true): static
    {
        $this->footerEnabled = $b;
        return $this;
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
        $row = Row::createRow($this->getTable()->getRow(), $headerLabels, 0);
        $row->setTemplate($template);
        $row->show();

        // Render table rows
        foreach ($this->getTable()->getList() as $rowId => $rowData) {
            $rowRepeat = $template->getRepeat('tr');
            // TODO: $rowId needs to add offset when using pager
            $row = Row::createRow($this->getTable()->getRow(), $rowData, $rowId+1);
            $row->setTemplate($rowRepeat);
            $row->show();
            $rowRepeat->appendRepeat();
        }

        $template->setAttr('table', $this->getTable()->getAttrList());
        $template->addCss('table', $this->getTable()->getCssList());

        if (count($this->getTable()->getList()) && $this->isFooterEnabled()) {
            /** @var UiInterface $item */
            foreach ($this->getFooterList() as $name => $item) {
                $tpl = $this->buildTemplate('footer-' . $name);
                if ($tpl) {
                    if ($this->getTable()->getList() instanceof Result) {
                        $item->initFromResult($this->getTable()->getList());
                    }
                    $item->setTemplate($tpl);
                    $template->appendTemplate('footer', $item->show());
                }
            }
            $template->setVisible('footer', $this->isFooterEnabled());
        }


        return $template;
    }
}