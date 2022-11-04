<?php
namespace Tk\Table\Cell;


use Dom\Renderer\Attributes;
use Dom\Template;

/**
 * @author Tropotek <http://www.tropotek.com/>
 */
class Text extends CellInterface
{

    /**
     * The max numbers of characters to display
     *      0 = no limit
     */
    protected int $charLimit = 0;

    protected bool $urlEnabled = true;

    protected Attributes $linkAttrs;


    public function __construct(string $name, string $label = '')
    {
        $this->linkAttrs = new Attributes();
        parent::__construct($name, $label);
    }

    /**
     * @return bool
     */
    public function isUrlEnabled(): bool
    {
        return $this->urlEnabled;
    }

    /**
     * @param bool $urlEnabled
     * @return Text
     */
    public function setUrlEnabled(bool $urlEnabled): Text
    {
        $this->urlEnabled = $urlEnabled;
        return $this;
    }

    /**
     * Use 0 to disable character limit
     */
    public function setCharacterLimit(int $i): static
    {
        $this->charLimit = $i;
        return $this;
    }

    public function getCharLimit(): int
    {
        return $this->charLimit;
    }

    public function getLinkAttrs(): Attributes
    {
        return $this->linkAttrs;
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

    public function show(): ?Template
    {
        // This is the cell repeat
        $template = $this->getTemplate();

        $template->insertHtml('td', $this->getValue());

        $this->decorate($template);
        return $template;
    }


}