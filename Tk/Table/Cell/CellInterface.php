<?php
namespace Tk\Table\Cell;

use Dom\Renderer\RendererInterface;
use Tk\ObjectUtil;
use Tk\Ui\Element;
use Dom\Renderer\Traits\RendererTrait;
use Dom\Template;
use Symfony\Component\HttpFoundation\Request;
use Tk\Db\Tool;
use Tk\CallbackCollection;
use Tk\Table;
use Tk\Table\Row;
use Tk\Ui\Link;
use Tk\Uri;

abstract class CellInterface extends Element implements RendererInterface
{
    use RendererTrait;

    const ORDER_NONE = '';
    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';

    protected Table $table;

    /**
     * Only gets set on start of rendering.
     * Accessible in the show() method calls
     */
    protected Row $row;

    protected string $name = '';

    protected string $value = '';

    protected string $label = '';

    protected bool $showLabel = false;

    protected bool $visible = true;

    protected string $orderByName = '';

    protected CallbackCollection $onValue;

    protected CallbackCollection $onShow;

    protected ?Link $link = null;

    protected ?Uri $url = null;

    protected string $urlProperty = 'id';


    public function __construct(string $name, string $label = '')
    {
        $this->onValue = CallbackCollection::create();
        $this->onShow = CallbackCollection::create();
        $this->name = $name;

        if (!$label) {  // Set the default label if none supplied
            $label = preg_replace('/(Id|_id)$/', '', $name);
            $label = str_replace(['_', '-'], ' ', $label);
            $label = ucwords(preg_replace('/[A-Z]/', ' $0', $label));
        }
        $this->setLabel($label);

    }

    /**
     * Execute any cell request functionality
     * This is called after the list is set and before the render methods are called
     */
    public function execute(Request $request): void { }

    public function showHeader(): ?Template
    {
        // This is the cell repeat
        $template = $this->getTemplate();
        if (!$this->getRow()->isHead()) return $template;

        $html = $this->getValue();
        $url = $this->getOrderByUrl();
        if ($url) {
            $html = sprintf('<a href="%s" class="noblock %s" title="%s">%s</a>',
                htmlentities($url->toString()),
                strtolower($this->getOrderByDir()),
                'Click to order by: ' . $this->getLabel(),
                $this->getLabel()
            );
        }

        $template->insertHtml('td', $html);
        $template->setAttr('td', $this->getAttrList());
        $template->addCss('td', $this->getCssList());
        return $template;
    }

    /**
     * A basic common cell renderer.
     */
    protected function decorate(Template $template): Template
    {
        $template->setAttr('td', $this->getAttrList());
        $template->addCss('td', $this->getCssList());

        // Add a URL to the cell content
        if ($this->getLink()) {
            $cellContent = $template->getVar('td')->nodeValue;
            $link = clone $this->getLink();
            $url = clone $this->getUrl();
            if ($this->getUrlProperty()) {
                $prop = ObjectUtil::getPropertyValue($this->getRow()->getData(), $this->getUrlProperty());
                $url->set($this->getUrlProperty(), $prop);
            }
            $link->setUrl($url);
            $html = $link->setText($cellContent);
            $template->insertHtml('td', $html);
        }

        return $template;
    }

    /**
     * Create an order by url for this cell.
     * This will create an orderBy URL, when clicked it will
     * redirect the page and updated the table order params as needed.
     */
    public function getOrderByUrl(): ?Uri
    {
        if (!$this->getOrderByName()) return null;

        $key = $this->getTable()->makeInstanceKey(Tool::PARAM_ORDER_BY);
        $url = Uri::create()->remove($key);

        $orderDir = $this->getNextOrderByDir();
        if ($orderDir) {
            $orderDir = $this->getOrderByName() . ' ' . $orderDir;
        }
        $url->set($key, $orderDir);

        return $url;
    }

    /**
     * get the current orderBy direction
     */
    protected function getOrderByDir(): string
    {
        $orderDir = self::ORDER_NONE;
        if ($this->getOrderByName() == $this->getTable()->getTableSession()->getOrderByName()) {
            $orderDir = $this->getTable()->getTableSession()->getOrderByDir();
        }
        return $orderDir;
    }

    protected function getNextOrderByDir(?string $orderDir = null): string
    {
        if ($orderDir === null) {
            $orderDir = $this->getOrderByDir();
        }

        // ASC first
        if ($orderDir == self::ORDER_ASC) {
            return self::ORDER_NONE;
        } else if ($orderDir == self::ORDER_DESC) {
            return self::ORDER_ASC;
        } else if ($orderDir == self::ORDER_NONE) {
            return self::ORDER_DESC;
        }

        // DESC first
//        if ($orderDir == self::ORDER_ASC) {
//            return self::ORDER_NONE;
//        } else if ($orderDir == self::ORDER_DESC) {
//            return  self::ORDER_ASC;
//        } else if ($orderDir == self::ORDER_NONE) {
//            return self::ORDER_DESC;
//        }
        return self::ORDER_NONE;
    }

    public function setTable(Table $table): static
    {
        $this->table = $table;
        return $this;
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    public function getRow(): Row
    {
        return $this->row;
    }

    public function setRow(Row $row): static
    {
        $this->row = $row;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): CellInterface
    {
        $this->value = $value;
        return $this;
    }

    public function setLabel(string $str): static
    {
        $this->label = $str;
        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setShowLabel(bool $b = true): static
    {
        $this->showLabel = $b;
        return $this;
    }

    public function showLabel(): bool
    {
        return $this->showLabel;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): static
    {
        $this->visible = $visible;
        return $this;
    }

    public function getOrderByName(): string
    {
        return $this->orderByName;
    }

    public function setOrderByName(string $orderByName): CellInterface
    {
        $this->orderByName = $orderByName;
        return $this;
    }

    public function getOnValue(): CallbackCollection
    {
        return $this->onValue;
    }

    /**
     * Called before the value is used, set this to change the table raw data value
     * Use this to set the cell properties before rendering
     *
     * Callback: function (CellInterface $cell, mixed $value) {  }
     */
    public function addOnValue(callable $callable, int $priority = CallbackCollection::DEFAULT_PRIORITY): static
    {
        $this->getOnValue()->append($callable, $priority);
        return $this;
    }

    public function getOnShow(): CallbackCollection
    {
        return $this->onShow;
    }

    /**
     * Called after the cell is decorated
     * Us this to modify the cell HTML after it has been rendered
     * Note: Use Cell::setUrl(null) to remove the automatic link markup
     *
     * Callback: function (CellInterface $cell, string $html) {  }
     */
    public function addOnShow(callable $callable, int $priority = CallbackCollection::DEFAULT_PRIORITY): static
    {
        $this->getOnShow()->append($callable, $priority);
        return $this;
    }

    public function setUrl(null|string|Uri $url): static
    {
        $this->url = $this->link = null;
        if (!empty($url)) {
            $this->link = new Link();
            $this->url = Uri::create($url);
        }
        return $this;
    }

    public function getUrl(): ?Uri
    {
        return $this->url;
    }

    public function getLink(): ?Link
    {
        return $this->link;
    }

    public function getUrlProperty(): string
    {
        return $this->urlProperty;
    }

    public function setUrlProperty(string $urlProperty): static
    {
        $this->urlProperty = $urlProperty;
        return $this;
    }

}
