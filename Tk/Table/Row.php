<?php
namespace Tk\Table;

use Dom\Renderer\RendererInterface;
use Tk\Ui\Traits\AttributesTrait;
use Tk\Ui\Traits\CssTrait;
use Dom\Renderer\Traits\RendererTrait;
use Dom\Template;
use Tk\Collection;
use Tk\Db\Mapper\Model;
use Tk\ObjectUtil;
use Tk\Table;
use Tk\Table\Cell\CellInterface;

class Row implements RendererInterface
{

    use AttributesTrait;
    use CssTrait;
    use RendererTrait;


    protected int $id = 0;

    protected Table $table;

    protected Collection $cells;

    protected bool $head = false;

    protected array|object $data;


    public function __construct(Table $table)
    {
        $this->table = $table;
        $this->cells = new Collection();
    }

    public static function createRow(Row $row, array|object $rowData, int $rowId): static
    {
        $obj = clone $row;
        $obj->cells = new Collection();
        $obj->data = $rowData;
        $obj->setId($rowId);
        $obj->init($rowData);
        return $obj;
    }

    protected function init(array|object $rowData)
    {
        $data = $rowData;
        if ($data instanceof Model) {
            /** @var Model $rowData */
            $data = [];
            $rowData->getMapper()->getTableMap()->loadArray($data, $rowData);
        }

        /** @var CellInterface $cell */
        foreach ($this->getTable()->getCells() as $orgCell) {
            $cell = clone $orgCell;
            $cell->setRow($this);

            $val = '';
            if (is_object($data)) {
                $val = ObjectUtil::getPropertyValue($data, $cell->getName()) ?? '';
            } elseif (is_array($data)) {
                $val = $data[$cell->getName()] ?? '';
            }

            $cell->setValue($val);

            $this->getCells()->set($cell->getName(), $cell);
        }
    }

    function show(): ?Template
    {
        // This is the row repeat or thead repeat
        $template = $this->getTemplate();

        if ($this->isHead()) {
            /** @var CellInterface $cell */
            foreach ($this->getCells() as $cell) {
                $cellTemplate = $template->getRepeat('td');
                $cell->addCss('mh' . ucfirst($cell->getName()));
                $cell->setAttr('title', $cell->getName());
                $cell->setTemplate($cellTemplate);
                $cell->showHeader();
                $cellTemplate->appendRepeat();
            }
        } else {
            /** @var CellInterface $cell */
            foreach ($this->getCells() as $cell) {
                $cellTemplate = $template->getRepeat('td');
                $cell->addCss('m' . ucfirst($cell->getName()));
                $cell->setAttr('title', $cell->getName());
                $cell->setTemplate($cellTemplate);
                $cell->show();
                $cellTemplate->appendRepeat();
            }
        }

        $template->setAttr('tr', $this->getAttrList());
        $template->addCss('tr', $this->getCssList());

        return $template;
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
        return $this->cells;
    }

    public function getData(): array|object
    {
        return $this->data;
    }
}