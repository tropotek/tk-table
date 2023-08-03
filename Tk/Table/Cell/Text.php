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
    protected int $maxLength = 0;


    public function __construct(string $name, string $label = '')
    {
        parent::__construct($name, $label);
    }

    public function setMaxLength(int $maxLength): static
    {
        $this->maxLength = $maxLength;
        return $this;
    }

    public function getMaxLength(): int
    {
        return $this->maxLength;
    }

    public function getCellValue(): string
    {
        $value = $this->getValue();
        if (is_null($value)) return '';

        if ($this->getMaxLength() && strlen($value) > $this->getMaxLength()) {
            $value = \Tk\Str::wordcat($value, $this->getMaxLength() - 3, '...');
        }

        return $value;
    }

}