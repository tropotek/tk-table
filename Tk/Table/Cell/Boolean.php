<?php
namespace Tk\Table\Cell;

use Dom\Template;

class Boolean extends Text
{
    public function show(): ?Template
    {
        // This is the cell repeat
        $template = $this->getTemplate();

        $this->setValue($this->getOnValue()->execute($this, $this->getValue()) ?? $this->getValue());

        $val = 'No';
        if ($this->getValue() === $this->getName() || strtolower($this->getValue()) === 'yes' || $this->getValue() == 1) {
            $val = 'yes';
        }

        $html = $val;
        $html = $this->getOnShow()->execute($this, $html) ?? $html;
        $template->insertHtml('td', $html);

        $this->decorate($template);

        return $template;
    }

}