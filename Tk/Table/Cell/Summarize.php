<?php
namespace Tk\Table\Cell;


/**
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Summarize extends Text
{

    /**
     * @param mixed $obj
     * @param int|null $rowIdx The current row being rendered (0-n) If null no rowIdx available.
     * @return string
     */
    public function getCellHtml($obj, $rowIdx = null)
    {
        if (!$this->hasAttr('title')) {
            $this->setAttr('title', htmlspecialchars($this->getCellHeader()));
        }

        $summary = htmlspecialchars(wordwrap(str_replace('. ', ".\n", $this->getPropertyValue($obj)), 80));
        $summary = str_replace("\n\n", "\n", $summary);
        if (!$summary) return '';
        $details = '';
        if ($summary) {
            $summary = trim($summary);
            $lines = explode("\n", $summary);
            if (count($lines) > 1) {
                $summary = array_shift($lines);
                $details = nl2br(implode("\n", $lines));
            }
        }

        $summary = htmlspecialchars($summary);
        $url = $this->getCellUrl($obj);
        if ($url && $this->isUrlEnabled()) {
            $summary = sprintf('<a href="%s" %s>%s</a>', htmlentities($url->toString()), $this->linkAttrs, $summary);
        }
        if (!$details) {
            return $summary;
        }

        $this->setUrlEnabled(true);     // Reset the urlEnabled status
        return sprintf('<details><summary>%s</summary>%s</details>', $summary, $details);
    }



}