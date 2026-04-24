<?php
namespace Tk\Table\Cell;

use Tk\Table;
use Tk\Table\Cell;

class RowSelect extends Cell
{
    protected string $property = '';
    protected bool $disabled = false;
    public string $html = '';

    public function __construct(string $name, string $property = '')
    {
        parent::__construct($name);
        $this->property = $property ?: $name;

        $this->addCss('text-center');
        $this->addHeaderCss('text-center');
    }

    public static function create(string $name, string $property = ''): self
    {
        return new self($name, $property);
    }

    public function getValue(): string
    {
        $row = $this->getRow();
        if (is_null($row)) return '';
        if (is_array($row)) $row = (object)$row;
        return $row->{$this->getProperty()} ?? '';
    }

    public function getHeader(): string
    {
        //$header = parent::getHeader();
        $disabled = $this->isDisabled() ? 'disabled' : '';
        return sprintf('<input type="checkbox" name="%s_all" title="Select All" class="tk-tcb-head" %s />', $this->getName(), $disabled);
    }

    public function getHtml(): string
    {
        $row = $this->getRow();
        if (is_null($row)) return '';
        if (is_array($row)) $row = (object)$row;

        $id = $this->getValue();
        $disabled = $this->isDisabled() ? 'disabled' : '';
        $this->html = sprintf('<input type="checkbox" name="%s[]" value="%s" class="tk-tcb" %s/>', $this->getName(), e($id), $disabled);

        $r = $this->getOnHtml()->execute($row, $this);
        if (!is_null($r) && is_string($r)) $this->html = $r;
        return $this->html;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    /**
     * @return array<int,string>
     */
    public function getSelected(): array
    {
        return $_POST[$this->getName()] ?? [];
    }

    public function setTable(?Table $table): Cell
    {
        if ($table instanceof Table) {
            $table->setAttr('data-row-select', $this->getName());
        }
        parent::setTable($table);
        return $this;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): self
    {
        $this->disabled = $disabled;
        return $this;
    }

}