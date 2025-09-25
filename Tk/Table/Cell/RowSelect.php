<?php
namespace Tk\Table\Cell;

use Tk\Table;
use Tk\Table\Cell;

class RowSelect extends Cell
{
    protected string $property = '';

    public function __construct(string $name, string $property = '')
    {
        parent::__construct($name);
        $this->property = $property ?: $name;

        $this->addCss('text-center');
        $this->addHeaderCss('text-center');
        $this->setHeader(sprintf('<input type="checkbox" name="%s_all" title="Select All" class="tk-tcb-head" />', $name));
    }

    public static function create(string $name, string $property = ''): self
    {
        return new self($name, $property);
    }

    /**
     * @param array<string,mixed>|object|null $row
     */
    public function getValue(null|array|object $row = null): string
    {
        if (is_null($row)) return '';
        if (is_array($row)) $row = (object)$row;
        return $row->{$this->getProperty()} ?? '';
    }

    /**
     * @param array<string,mixed>|object|null $row
     */
    public function getHtml(null|array|object $row = null): string
    {
        if (is_null($row)) return '';
        if (is_array($row)) $row = (object)$row;
        $id = $this->getValue($row);
        return sprintf('<input type="checkbox" name="%s[]" value="%s" class="tk-tcb"/>', $this->getName(), e($id));
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

}