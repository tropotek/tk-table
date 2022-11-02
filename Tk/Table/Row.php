<?php
namespace Tk\Table;

use Dom\Renderer\Traits\AttributesTrait;
use Dom\Renderer\Traits\CssTrait;
use Tk\CollectionTrait;

/**
 * This class holds the row template values
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class Row
{

    use AttributesTrait;
    use CssTrait;
    use CollectionTrait;

    protected bool $head = true;

    protected int $rowId = 0;


    public function __construct()
    {
        $this->_CollectionTrait();
    }

    public function getRowId(): int
    {
        return $this->rowId;
    }

    public function setRowId(int $rowId): Row
    {
        if ($rowId > 0) $this->setHead(false);
        $this->rowId = $rowId;
        return $this;
    }

    public function isHead(): bool
    {
        return $this->head;
    }

    public function setHead(bool $head): Row
    {
        $this->head = $head;
        return $this;
    }

    public function resetRow(): static
    {
        $this->setAttrList([]);
        $this->setCssList();
        $this->getCollection()->clear();
        return $this;
    }
}