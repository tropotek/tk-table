<?php
namespace Tk\Table\Cell;

use Dom\Template;
use Tk\Ui\Link;
use Tk\Uri;

class Date extends CellInterface
{
    const FORMAT_RELATIVE = 'relative';

    protected string $format = 'Y-m-d h:i:s';


    public function __construct(string $name, ?string $format = null)
    {
        parent::__construct($name);
        if ($format) {
            $this->format = $format;
        }
    }

    public function getCellValue(): string
    {
        $value = $this->getValue();
        if (is_null($value)) return '';

        if ($value instanceof \DateTime) {
            if ($this->getFormat() == self::FORMAT_RELATIVE) {
                return \Tk\Date::toRelativeString($value);
            }
            return $value->format($this->getFormat());
        }
        return $value;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(string $format): Date
    {
        $this->format = $format;
        return $this;
    }

}