<?php
namespace Tk\Table\Cell;

class Summarize extends Text
{

    public function __construct(string $name, string $label = '')
    {
        parent::__construct($name, $label);

        $this->addOnShow([$this, 'cellShow']);
    }

    public function cellShow(CellInterface $cell, string $html): string
    {
        $value = $this->getCellValue();

        $summary = htmlspecialchars(wordwrap(str_replace('. ', ".\n", $value), 80));
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
        $html = $summary;
        if ($details) {
            $html = sprintf('<details><summary>%s</summary>%s</details>', $summary, $details);
        }

        return $html;
    }

}