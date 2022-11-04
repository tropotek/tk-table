<?php
namespace Tk\Table\Cell;

use Dom\Renderer\RendererInterface;
use Dom\Renderer\Traits\AttributesTrait;
use Dom\Renderer\Traits\CssTrait;
use Dom\Renderer\Traits\RendererTrait;
use Dom\Template;
use Symfony\Component\HttpFoundation\Request;
use Tk\Traits\SystemTrait;
use Tk\CallbackCollection;
use Tk\Table;
use Tk\Table\Row;


/**
 * @author Tropotek <http://www.tropotek.com/>
 */
abstract class CellInterface implements RendererInterface
{
    use SystemTrait;
    use AttributesTrait;
    use CssTrait;
    use RendererTrait;

    protected Table $table;

    /**
     * Only gets set on start of rendering.
     * Accessible in teh show() method calls
     */
    protected Row $row;

    protected string $name = '';

    protected string $value = '';

    protected string $label = '';

    protected bool $showLabel = false;

    protected bool $visible = true;

    protected CallbackCollection $onShow;


    public function __construct(string $name, string $label = '')
    {
        $this->onShow = CallbackCollection::create();
        $this->name = $name;

        if (!$label) {  // Set the default label if none supplied
            $label = preg_replace('/Id$/', '', $name);
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

    /**
     * A basic common cell renderer.
     */
    protected function decorate(Template $template): Template
    {
        // TODO ...

        $this->getOnShow()->execute($this, $template);

        $template->setAttr('td', $this->getAttrList());
        $template->addCss('td', $this->getCssList());

        return $template;
    }



//    /**
//     * @param mixed $obj
//     * @param int|null $rowIdx The current row being rendered (0-n) If null no rowIdx available.
//     * @return string
//     */
//    public function getCellHtml($obj, $rowIdx = null)
//    {
//        $value = $propValue = $this->getPropertyValue($obj);
//        if ($this->charLimit && strlen($propValue) > $this->charLimit) {
//            $propValue = \Tk\Str::wordcat($propValue, $this->charLimit - 3, '...');
//        }
//        //if (!$this->hasAttr('title') && (!is_array($value) && !is_object($value))) {
//        if (!$this->hasAttr('title')) {
//            //$this->setAttr('title', htmlentities($propValue));
//            $this->setAttr('title', htmlspecialchars($value));
//        }
//
//        $str = htmlspecialchars($propValue);
//        $url = $this->getCellUrl($obj);
//        if ($url && $this->isUrlEnabled()) {
//            $str = sprintf('<a href="%s" %s>%s</a>', htmlentities($url->toString()), $this->linkAttrs, htmlspecialchars($propValue));
//        }
//
//        $this->setUrlEnabled(true);     // Reset the urlEnabled status
//        return $str;
//    }


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

    public function setShowLabel(bool $b = true): static
    {
        $this->showLabel = $b;
        return $this;
    }

    public function showLabel(): bool
    {
        return $this->showLabel;
    }

    public function getLabel(): string
    {
        return $this->label;
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

    public function getOnShow(): CallbackCollection
    {
        return $this->onShow;
    }

    /**
     * Callback: function (CellInterface $cell, Template $template) { return $template; }
     */
    public function addOnShow(callable $callable, int $priority = CallbackCollection::DEFAULT_PRIORITY): static
    {
        $this->getOnShow()->append($callable, $priority);
        return $this;
    }

}
