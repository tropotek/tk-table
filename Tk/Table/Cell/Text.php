<?php
namespace Tk\Table\Cell;

use Dom\Template;
use Tk\Ui\Link;
use Tk\Uri;

class Text extends CellInterface
{

    /**
     * The max numbers of characters to display
     *      0 = no limit
     */
    protected int $charLimit = 0;


    public function __construct(string $name, string $label = '')
    {
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

    public function show(): ?Template
    {
        // This is the cell repeat
        $template = $this->getTemplate();

        $propValue = $this->getValue();
        if ($this->charLimit && strlen($propValue) > $this->charLimit) {
            $propValue = \Tk\Str::wordcat($propValue, $this->charLimit - 3, '...');
        }

        $html = $propValue;
        $template->insertHtml('td', $html);

        $this->decorate($template);

        return $template;
    }

}