<?php
namespace Tk\Table\Cell;


use Dom\Template;

/**
 * @author Tropotek <http://www.tropotek.com/>
 */
class Summarize extends Text
{

    public function show(): ?Template
    {
        // This is the cell repeat
        $template = $this->getTemplate();

        $this->decorate($template);

        $summary = htmlspecialchars(wordwrap(str_replace('. ', ".\n", $this->getValue()), 80));
        $summary = str_replace("\n\n", "\n", $summary);
        $details = '';
        if ($summary) {
            $summary = trim($summary);
            $lines = explode("\n", $summary);
            if (count($lines) > 1) {
                $summary = array_shift($lines);
                $details = nl2br(implode("\n", $lines));
            }
        }

        if ($this->getUrl()) {
            $this->getLink()->setUrl($this->getUrl());
            $summary = $this->getLink()->setText($summary);
        }
        $html = $summary;
        if ($details) {
            $html = sprintf('<details><summary>%s</summary>%s</details>', $summary, $details);
        }

        $template->insertHtml('td', $html);
        return $template;
    }

}