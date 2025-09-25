<?php
namespace Tk\Table;

use Tk\CallbackCollection;
use Tk\Str;
use Tk\Ui\Attributes;
use Tk\Ui\Traits\AttributesTrait;
use Tk\Uri;
use Tk\Table;

class Cell
{
    use AttributesTrait;

    protected string     $name        = '';
    protected ?string    $value       = null;
    protected string     $header      = '';
    protected string     $orderBy     = '';
    protected bool       $sortable    = false;
    protected ?Table     $table       = null;

    protected Attributes $headerAttrs;
    protected CallbackCollection $onValue;
    protected CallbackCollection $onHtml;


    public function __construct(string $name, string $header = '')
    {
        $this->name    = $name;
        $this->orderBy = Str::toSnake($name);
        $this->onValue = CallbackCollection::create();
        $this->onHtml  = CallbackCollection::create();
        $this->headerAttrs = new Attributes();

        $this->addCss('m'.ucfirst($name));
        $this->headerAttrs->addCss('mh'.ucfirst($name));

        if (!$header) {  // Set the default header label if none supplied
            $header = strval(preg_replace('/(Id|_id)$/', '', $name));
            $header = str_replace(['_', '-'], ' ', $header);
            $header = ucwords(strval(preg_replace('/[A-Z]/', ' $0', $header)));
        }
        $this->setHeader($header);
    }

    /**
     * called by Table::execute()
     */
    public function execute(): void { }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRowAttrs(): Attributes
    {
        if (!$this->getTable()) {
            throw new \Exception('Table not set for cell '.$this->getName());
        }
        return $this->getTable()->getRowAttrs();
    }

    /**
     * Callbacks are executed when getValue() is called
     * @callable function (array|object $row, Cell $cell) {  }
     */
    public function addOnValue(callable $callable, int $priority = CallbackCollection::DEFAULT_PRIORITY): static
    {
        $this->getOnValue()->append($callable, $priority);
        return $this;
    }

    /**
     * an array of callable types, called with call_user_func_array()
     */
    public function getOnValue(): CallbackCollection
    {
        return $this->onValue;
    }

    /**
     * Return the value of a cell, not a HTML rendered value
     * This value should be valid for any table output, CSV, PDF, HTML, etc
     *
     * @param array<string,mixed>|object|null $row
     */
    public function getValue(null|array|object $row = null): mixed
    {
        if (!is_null($row)) {
            if (is_array($row)) $row = (object)$row;
            $value = $this->getOnValue()->execute($row, $this);
            if (!is_null($value)) return $value;
        }
        if (!is_null($this->value)) return $this->value;
        return $row->{$this->getName()} ?? null;
    }

    public function setValue(mixed $value): static
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Callbacks are executed when getHtml() is called by a renderer
     * @callable function (array|object $row, Cell $cell) {  }
     */
    public function addOnHtml(callable $callable, int $priority = CallbackCollection::DEFAULT_PRIORITY): static
    {
        $this->getOnHtml()->append($callable, $priority);
        return $this;
    }

    /**
     * an array of callable types, called with call_user_func_array()
     */
    public function getOnHtml(): CallbackCollection
    {
        return $this->onHtml;
    }

    /**
     * Return a HTML representation of the value.
     * This will be called by the renderer for HTML rendered tables
     *
     * @param array<string,mixed>|object|null $row
     */
    public function getHtml(null|array|object $row = null): mixed
    {
        $value = $this->getValue($row);
        $html = $this->getOnHtml()->execute($row, $this);
        if (!is_null($html)) return $html;
        return $value;
    }

    public function setHeader(string $header): static
    {
        $this->header = $header;
        return $this;
    }

    public function getHeader(): string
    {
        return $this->header;
    }

    public function getHeaderAttrs(): Attributes
    {
        return $this->headerAttrs;
    }

    public function setHeaderAttrs(Attributes $headerAttrs): static
    {
        $this->headerAttrs = $headerAttrs;
        return $this;
    }

    public function addHeaderCss(string $css): static
    {
        $this->headerAttrs->addCss($css);
        return $this;
    }

    /**
     * @param array<string,string>|string $name
     */
    public function setHeaderAttr(array|string $name, ?string $value = null): static
    {
        $this->headerAttrs->setAttr($name, $value);
        return $this;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function setSortable(bool $sortable): static
    {
        $this->sortable = $sortable;
        return $this;
    }

    public function getOrderBy(): string
    {
        return $this->orderBy;
    }

    public function setOrderBy(string $orderBy): static
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    public function getTable(): ?Table
    {
        return $this->table;
    }

    public function setTable(?Table $table): Cell
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Get the order by url for this cell.
     * This will create an orderBy URL, when clicked it will
     * redirect the page and update the table order to the opposite order
     */
    public function getOrderByUrl(): ?Uri
    {
        if (!$this->getTable()) return null;
        if (!$this->isSortable()) return null;

        $key = $this->getTable()->makeRequestKey(Table::PARAM_ORDERBY);
        $url = Uri::create()->remove($key);
        $orderBy = $this->getOrderBy();

        $col = $this->getTable()->getOrderBy();
        $dir = '-';
        if ($col && $col[0] == '-') {
            $col = substr($col, 1);
            $dir = '';
        }

        if ($col == $orderBy) {
            if ($dir == '-') {
                $url->set($key, $dir.$col);
            } else {
                $url->set($key, '');
            }
        } else {
            $url->set($key, $orderBy);
        }

        return $url;
    }

}