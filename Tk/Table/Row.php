<?php
namespace Tk\Table;

/**
 * This class holds the row template values
 *
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @see http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Row
{

    use \Tk\Dom\AttributesTrait;
    use \Tk\Dom\CssTrait;
    use \Tk\CollectionTrait;

    protected $head = true;

    protected $rowId = 0;


    public function __construct()
    {
    }

    /**
     * @return int
     */
    public function getRowId(): int
    {
        return $this->rowId;
    }

    /**
     * @param int $rowId
     * @return Row
     */
    public function setRowId(int $rowId): Row
    {
        if ($rowId > 0) $this->setHead(false);
        $this->rowId = $rowId;
        return $this;
    }

    /**
     * @return bool
     */
    public function isHead(): bool
    {
        return $this->head;
    }

    /**
     * @param bool $head
     * @return Row
     */
    public function setHead(bool $head): Row
    {
        $this->head = $head;
        return $this;
    }

    /**
     * reset all the attrs and css and data back to its default state
     * @todo: Look into if this could replace the Cell hack to add css and attrs to the row.
     *
     * @return $this
     */
    public function resetRow()
    {
        $this->setAttrList();
        $this->setCssList();
        $this->getCollection()->clear();
        return $this;
    }
}