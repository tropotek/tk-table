<?php
namespace Tk\Table\Cell;

use Dom\Template;

class Boolean extends Text
{
    public function show(): ?Template
    {
        // This is the cell repeat
        $template = $this->getTemplate();

        $val = 'No';
        if ($this->getValue() === $this->getName() || strtolower($this->getValue()) === 'yes' || $this->getValue() == 1) {
            $val = 'yes';
        }

        $template->insertHtml('td', $val);

        $this->decorate($template);

        return $template;
    }

}