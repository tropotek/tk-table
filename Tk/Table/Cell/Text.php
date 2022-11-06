<?php
namespace Tk\Table\Cell;


use Dom\Template;
use Tk\Ui\Link;
use Tk\Uri;

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

    protected string $urlProperty = 'id';

    protected Link $link;


    public function __construct(string $name, string $label = '')
    {
        $this->link = new Link();
        parent::__construct($name, $label);
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

    public function getLink(): Link
    {
        return $this->link;
    }

    public function show(): ?Template
    {
        // This is the cell repeat
        $template = $this->getTemplate();

        $this->decorate($template);

        $propValue = $this->getValue();
        if ($this->charLimit && strlen($propValue) > $this->charLimit) {
            $propValue = \Tk\Str::wordcat($propValue, $this->charLimit - 3, '...');
        }

        $html = $propValue;
        if ($this->getUrl()) {
            $this->getLink()->setUrl($this->getUrl());
            $html = $this->getLink()->setText($propValue)->getHtml();
        }
        $template->insertHtml('td', $html);

        return $template;
    }


}