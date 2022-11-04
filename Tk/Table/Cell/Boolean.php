<?php
namespace Tk\Table\Cell;


use Dom\Template;

/**
 * @author Tropotek <http://www.tropotek.com/>
 */
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

//    /**
//     * Get the raw string property value with no formatting.
//     * This call can be used for exporting data into a csv, json, xml format
//     *
//     * @param object $obj
//     * @return string
//     */
//    public function getPropertyValue(object $obj)
//    {
//        $value = $this->getObjectPropertyValue($obj);
//        $v = 'No';
//        if ($value) {
//            if ($value == true || strtolower($value) == 'yes' || strtolower($value) == 'true' ||
//                strtolower($value) == 't' || $value == '1' || strtolower($value) == 'ok' || strtolower($value) == 'y' ||
//                $value == $this->getProperty())
//            {
//                $v = 'Yes';
//            }
//        }
//        return $v;
//    }

}