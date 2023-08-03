<?php
namespace Tk\Table\Cell;

use Dom\Template;

class Boolean extends Text
{

    public function getCellValue(): string
    {
        $value = $this->getValue();
        if (is_null($value)) return '';

        if (!is_bool($value)) {
            $value = false;
            if ($this->getValue() == $this->getName() ||
                strtolower($this->getValue()) == 'yes' ||
                strtolower($this->getValue()) == 'true' ||
                $this->getValue() == 1
            ) {
                $value = true;
            }
        }

        return $value ? 'Yes' : 'No';
    }

}