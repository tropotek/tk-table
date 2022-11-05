<?php
namespace Tk\Table;

use Dom\Renderer\RendererInterface;
use Dom\Renderer\Traits\AttributesTrait;
use Dom\Renderer\Traits\CssTrait;
use Dom\Renderer\Traits\RendererTrait;
use Dom\Template;
use Tk\Collection;
use Tk\Table;
use Tk\Table\Cell\CellInterface;

/**
 * @author Tropotek <http://www.tropotek.com/>
 */
class Row implements RendererInterface
{

    use AttributesTrait;
    use CssTrait;
    use RendererTrait;


    protected int $id = 0;

    protected Table $table;

    protected Collection $cells;

    protected bool $head = false;

    protected array $data = [];


    public function __construct(Table $table)
    {
        $this->table = $table;
        $this->cells = new Collection();
    }

    public static function createRow(Row $row, array $rowData, int $rowId): static
    {
        $obj = clone $row;
        $obj->cells = new Collection();
        $obj->data = $rowData;
        $obj->setId($rowId);
        $obj->init($rowData);

        return $obj;
    }

    protected function init(array $rowData)
    {
        /** @var CellInterface $cell */
        foreach ($this->getTable()->getCells() as $cell) {
            $nc = clone $cell;
            $nc->setRow($this);
            $nc->setValue($rowData[$nc->getName()] ?? '');
            $this->getCells()->set($nc->getName(), $nc);
        }
    }


    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Row
    {
        if ($id == 0) $this->head = true;
        $this->id = $id;
        return $this;
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    public function isHead(): bool
    {
        return $this->head;
    }

    public function getCells(): Collection
    {
        return$this->cells;
    }

    public function getData(): array
    {
        return $this->data;
    }


    function show(): ?Template
    {
        // This is the row repeat or thead repeat
        $template = $this->getTemplate();

        if ($this->isHead()) {
            /** @var CellInterface $cell */
            foreach ($this->getCells() as $cell) {
                $cellTemplate = $template->getRepeat('td');
                $cell->setTemplate($cellTemplate);
                $cell->showHeader();
                $cellTemplate->appendRepeat();
            }

            return $template;
        }

        /** @var CellInterface $cell */
        foreach ($this->getCells() as $cell) {
            $cellTemplate = $template->getRepeat('td');
            $cell->setTemplate($cellTemplate);
            $cell->show();
            $cellTemplate->appendRepeat();
        }

        $template->setAttr('tr', $this->getAttrList());
        $template->addCss('tr', $this->getCssList());

        return $template;
    }
}